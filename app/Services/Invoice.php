<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class Invoice
{
    protected $invoiceId;

    protected $type = '';
    protected $types = ['deposit', 'interim', 'final', 'cancelled'];

    protected $date;

    protected $vats = [];
    protected $netTotal = 0;
    protected $grossTotal = 0;

    protected $updatedFields = [];

    protected $order;

    public function ofType(string $type)
    {
        if (!in_array($type, $this->types)) {
            throw new \Exception('Unknown Invoice Type: ' . $this->type ?? 'null');
        }

        $this->type = $type;
        return $this;
    }

    public function forOrder(Order $order)
    {
        $this->order = $order;
        $this->setDate();
        $this->setInvoiceId();

        return $this;
    }

    public function updatedFields()
    {
        return $this->updatedFields;
    }

    public function invoiceId()
    {
        return $this->invoiceId;
    }

    public function makePdf()
    {
        return PDF::view("pdf.invoice_{$this->type}", [
            'date' => $this->date,
            'venue' => $this->order->venue,
            'customer' => $this->order->customer,
            'order' => $this->order,
        ])->setOptions([
            'enable-local-file-access' => true,
        ]);
    }

    public function makeHtml()
    {
        return view("pdf.invoice_{$this->type}", [
            'date' => $this->date,
            'venue' => $this->order->venue,
            'customer' => $this->order->customer,
            'order' => $this->order,
        ]);
    }

    protected function setDate()
    {
        $at_field = $this->type . '_invoice_at';

        $this->date = $this->order->$at_field;

        if ($this->date === null) {
            $this->date = Carbon::now();
            $this->updatedFields[$at_field] = $this->date;
        }

        return $this;
    }

    protected function setInvoiceId()
    {
        $id_field = $this->type . '_invoice_id';

        $this->invoiceId = $this->order->$id_field;

        if ($this->invoiceId === null) {
            $this->invoiceId = $this->order->venue->getNextInvoiceId();
            $this->updatedFields[$id_field] = $this->invoiceId;
        }

        return $this;
    }
}
