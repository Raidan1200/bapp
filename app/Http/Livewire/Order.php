<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\Invoice;
use Illuminate\Support\Carbon;
use App\Events\OrderHasChanged;
use App\Mail\ConfirmationEmail;
use App\Models\Order as OrderModel;
use Illuminate\Support\Facades\Mail;
use App\Events\InvoiceEmailRequested;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Order extends Component
{
    use AuthorizesRequests;

    // TODO TODO move into DB:Venue
    private const PAYMENT_DELAY = 7;

    public $order;

    public $notes;
    public $selectedState;
    public $cash;

    public $dirty = false;

    protected $colors = [
        'fresh' => 'border-gray-400',
        'overdue' => 'border->red->400',
        'deposit_paid' => 'border-yellow-500',
        'interim_paid' => 'border-green-500',
        'final_paid' => 'border-blue-500',
        'cancelled' => 'border-gray-400',
        'not_paid' => 'border-gray-400',
    ];

    public $rules = [
        'order.cash_payment' => 'required|boolean',
        'order.state' => 'required|string',
    ];

    protected $listeners = [
        'updateBookings' => 'bookingsUpdated',       //$refresh',
        'updateItems'    => 'itemsUpdated',          //$refresh',
        'updateCustomer' => 'logCustomerDataChange', //$refresh'
    ];

    public function mount($order)
    {
        $this->order = $order;
        $this->notes = $order->notes;
        $this->selectedState = $order->state;
        $this->cash = $order->cash_payment;
    }

    public function render()
    {
        return view('livewire.order');
    }

    public function updated()
    {
        $this->dirty = true;
    }

    public function save()
    {
        $this->authorize('modify orders', $this->order);

        $this->handleStateChange();
        $this->logNotesChange();

        $this->order->update(
            array_merge([
                'notes' => $this->notes,
                'state' => $this->selectedState,
                'cash_payment' => $this->cash,
            ], $this->updatedTimestamps())
        );

        $this->dirty = false;
        $this->editingNote = false;
    }

    public function cancel()
    {
        $this->notes = $this->order->notes;
        $this->selectedState = $this->order->state;
        $this->cash = $this->order->cash_payment;

        $this->dirty = false;
        $this->editingNote = false;
    }

    public function getColorProperty() : string
    {
        // if (
        //     (
        //         ($this->order->interim_email_at && ! $this->order->interim_paid_at)
        //         ||
        //         ($this->order->final_email_at && ! $this->order->final_paid_at)
        //     )
        //     &&
        //     now()->greaterThan($this->order->interim_email_at->addDays(self::PAYMENT_DELAY))
        // ) {
        //     return $this->colors['overdue'];
        // }

        // if ($this->order->interim_is_final && $this->order->interim_paid_at) {
        //     return $this->colors['final_paid'];
        // }

        $color = $this->colors[$this->order->state] ?? '';

        if (
            $this->order->state === 'interim_paid' &&
            $this->order->interim_is_final &&
            $this->order->starts_at->endOfDay()->lessThan(now())
        ) {
            $color = $this->colors['final_paid'];
        }

        return $color;
    }

    public function updatedTimestamps() : array
    {
        if (! $this->stateWillChange()) {
            return [];
        }

        $timestamps = [];

        switch ($this->selectedState) {
            case 'fresh':
                // $timestamps['deposit_paid_at'] = null;
                // $timestamps['interim_paid_at'] = null;
                // $timestamps['final_paid_at'] = null;
                // $timestamps['cancelled_at'] = null;
                break;

            case 'deposit_paid':
                $timestamps['deposit_paid_at'] = Carbon::now();
                // $timestamps['interim_paid_at'] = null;
                // $timestamps['final_paid_at'] = null;
                // $timestamps['cancelled_at'] = null;
                break;

            case 'interim_paid':
                $timestamps['interim_paid_at'] = Carbon::now();
                // $timestamps['final_paid_at'] = null;
                // $timestamps['cancelled_at'] = null;
                break;

            case 'final_paid':
                $timestamps['final_paid_at'] = Carbon::now();
                // $timestamps['cancelled_at'] = null;
                break;

            case 'cancelled':
                $timestamps['cancelled_at'] = Carbon::now();
                break;

            case 'not_paid':
                break;
        }

        return $timestamps;
    }

    public function makeInvoice(string $type)
    {
        $order = OrderModel::findOrFail($this->order->id)->load('venue');

        // TODO INVOICE GENERATION This sucks ... make it shorter!!!
        return (new Invoice)
            ->ofType($type)
            ->forOrder($order)
            ->asStream()
            ->makePdf();
    }

    public function sendEmail(string $type)
    {
        $order = OrderModel::findOrFail($this->order->id)->load('venue');

        // TODO INVOICE GENERATION This sucks ... make it shorter!!!
        // TODO TODO TODO THIS REALLY sucks because its TOOO easy to forget
        // the call to updatedFields which will result in chaos and destruction!!!
        $invoice = (new Invoice)
            ->ofType($type)
            ->forOrder($order)
            ->asString()
            ->makePdf();

        $email = Mail::to($this->order->customer->email);

        $emailClass = '\\App\\Mail\\' . ucfirst($type) . 'Email';
        $email_sent_field = $type . '_email_at';

        // LATER: How do I handle mail-sent errors ... try catch?
        // LATER: queue throws 'Attempt to read property "name" on null in /var/www/html/storage/framework/views/ed2533...'
        $email->send(new $emailClass($order, $invoice));

        if ($type !== 'cancelled') {
            $order->update([
                $email_sent_field => Carbon::now()
            ]);
        }
    }

    public function sendConfirmationEmail()
    {
        $order = OrderModel::findOrFail($this->order->id)->load('venue');

        Mail::to($this->order->customer->email)
            // LATER: queue throws 'Attempt to read property "name" on null in /var/www/html/storage/framework/views/ed2533...'
            ->send(new ConfirmationEmail($order));
    }

    public function stateWillChange()
    {
        return $this->order->state !== $this->selectedState;
    }

    public function handleStateChange()
    {
        if ($this->stateWillChange()) {
            $this->logStateChange();

            if ($this->order->state === 'fresh' && $this->selectedState === 'deposit_paid') {
                $this->sendConfirmationEmail();
            }

            if ($this->order->state === 'deposit_paid' && $this->selectedState === 'interim_paid') {
            }

            if ($this->selectedState === 'final_paid') {
            }

            if ($this->selectedState === 'cancelled') {
            }

            $this->updatePaymentChecks();
        }
    }

    public function updatePaymentChecks()
    {
        if (
            $this->order->state === 'fresh' &&
            $this->selectedState !== 'fresh' &&
            $this->order->needs_check
        ) {
            $this->order->update(['needs_check' => false]);
            $this->order->venue->decrement('check_count');
        }
    }

    public function bookingsUpdated()
    {
        $bookingData['starts_at'] = $this->firstBookingDate($this->order->bookings);

        if ($this->order->deposit_paid_at === null) {
            $bookingData['deposit_amount'] = $this->order->deposit;
        }

        if ($this->order->interim_paid_at === null) {
            $bookingData['interim_amount'] = $this->order->grossTotal - $this->order->deposit;
        }

        if ($this->order->deposit_paid_at && $this->order->interim_paid_at) {
            $bookingData['interim_is_final'] = false;
        }

        $this->order->update($bookingData);

        $this->logBookingsChange();
    }

    // LATER: Duplicated in Livewire\Order ... BAAAAD!!!
    protected function firstBookingDate($bookings)
    {
        return new Carbon(
            (collect($bookings)
                ->pluck('starts_at')
                ->filter(function($b) { return $b !== null; })
            )
            ->sort()
            ->values()
            ->first()
        );
    }

    public function itemsUpdated()
    {
        $itemData = [];

        if ($this->order->interim_paid_at === null) {
            $itemData['interim_amount'] = $this->order->grossTotal - $this->order->deposit_amount;
        }

        if ($this->order->interim_paid_at) {
            $itemData['interim_is_final'] = false;
        }

        $this->order->update($itemData);
    }


    ////////////////
    // Action Log //
    ////////////////
    public function logStateChange()
    {
        if ($this->stateWillChange()) {
            OrderHasChanged::dispatch($this->order, auth()->user(), 'state', $this->order->state, $this->selectedState);
        }
    }

    public function logNotesChange()
    {
        if ($this->order->notes !== $this->notes) {
            OrderHasChanged::dispatch($this->order, auth()->user(), 'notes', $this->order->notes ?? '', $this->notes);
        }
    }

    public function logCustomerDataChange()
    {
        OrderHasChanged::dispatch($this->order, auth()->user(), 'customer', '', '');
    }

    public function logBookingsChange()
    {
        OrderHasChanged::dispatch($this->order, auth()->user(), 'bookings', $this->order->bookings->count(), '');
    }

    public function logItemsChange()
    {
        OrderHasChanged::dispatch($this->order, auth()->user(), 'items', $this->order->items->count(), '');
    }
}
