<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryReservation;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class InventoryReservationService
{
    public function reserve(Order $order): void
    {
        foreach ($order->items as $item) {
            $inventory = Inventory::query()->where('outlet_id', $order->outlet_id)->where('product_id', $item->product_id)->lockForUpdate()->first();
            $available = $inventory ? $inventory->quantity - $inventory->reserved_quantity : 0;
            throw_if($available < $item->quantity, ValidationException::withMessages(['cart' => ["Insufficient stock for {$item->product_name}."]]));

            $inventory->increment('reserved_quantity', $item->quantity);
            InventoryReservation::create(['order_id' => $order->id, 'inventory_id' => $inventory->id, 'quantity' => $item->quantity, 'status' => 'reserved', 'expires_at' => now()->addMinutes(15)]);
        }
    }

    public function commit(Order $order): void
    {
        foreach (InventoryReservation::query()->where('order_id', $order->id)->where('status', 'reserved')->lockForUpdate()->get() as $reservation) {
            $inventory = Inventory::query()->lockForUpdate()->findOrFail($reservation->inventory_id);
            $inventory->decrement('quantity', $reservation->quantity);
            $inventory->decrement('reserved_quantity', $reservation->quantity);
            $reservation->update(['status' => 'committed', 'committed_at' => now()]);
        }
    }

    public function release(Order $order): void
    {
        foreach (InventoryReservation::query()->where('order_id', $order->id)->where('status', 'reserved')->lockForUpdate()->get() as $reservation) {
            Inventory::query()->whereKey($reservation->inventory_id)->decrement('reserved_quantity', $reservation->quantity);
            $reservation->update(['status' => 'released', 'released_at' => now()]);
        }
    }
}

