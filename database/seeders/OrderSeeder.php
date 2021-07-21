<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Venue;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $venues = Venue::with('rooms.products')->get();

        foreach ($venues as $venue) {
            $this->makeOrdersFor($venue);
        }
    }

    public function makeOrdersFor($venue) {
        $rooms = $venue->rooms;

        for ($i = 1; $i <= 10; $i++) {
            $order = Order::factory()->create([
                'venue_id' => $venue->id
            ]);
            Booking::factory()->create([
                'room_id' => $rooms[0]->id,
                'product_id' => $rooms[0]->products[0]->id,
                'quantity' => rand(30, 50),
                'order_id' => $order->id,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $order = Order::factory()->create([
                'venue_id' => $venue->id
            ]);
            Booking::factory()->create([
                'room_id' => $rooms[0]->id,
                'product_id' => $rooms[0]->products[1]->id,
                'quantity' => rand(30, 50),
                'order_id' => $order->id,
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $order = Order::factory()->create([
                'venue_id' => $venue->id
            ]);
            Booking::factory()->create([
                'room_id' => $rooms[1]->id,
                'product_id' => $rooms[1]->products[0]->id,
                'quantity' => rand(30, 50),
                'order_id' => $order->id,
            ]);
        }

        for ($i = 1; $i <= 20; $i++) {
            $order = Order::factory()->create([
                'venue_id' => $venue->id
            ]);
            Booking::factory()->create([
                'starts_at' => $start = Carbon::now()->setHour(17 + rand(0, 2))->addDays(rand(1, 4)),
                'ends_at' => $end = $start->addHours(rand(1, 2)),
                'room_id' => $rooms[0]->id, 'product_id' => $rooms[0]->products[0]->id, 'order_id' => $order->id, 'quantity' => 25,
            ]);
            Booking::factory()->create([
                'starts_at' => $end,
                'ends_at' => $end->addHours(rand(1, 2)),
                'room_id' => $rooms[1]->id, 'product_id' => $rooms[1]->products[0]->id, 'order_id' => $order->id, 'quantity' => 25,
            ]);
        }
    }
}
