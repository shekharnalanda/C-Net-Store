<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderStatusService
{
    private const ALLOWED = [
        'confirmed' => ['accepted', 'cancelled'],
        'accepted' => ['preparing', 'cancelled'],
        'preparing' => ['ready_for_pickup', 'cancelled'],
        'ready_for_pickup' => ['out_for_delivery', 'cancelled'],
        'out_for_delivery' => ['delivered'],
    ];

    public function transition(Order $order, OrderStatus $to, ?User $actor, ?string $note = null, array $metadata = []): Order
    {
        $from = $order->status->value;
        throw_unless(in_array($to->value, self::ALLOWED[$from] ?? [], true), ValidationException::withMessages(['status' => ["Order cannot move from {$from} to {$to->value}."]]));
        $order->update(['status' => $to]);
        $order->statusHistory()->create(['from_status' => $from, 'to_status' => $to->value, 'actor_id' => $actor?->id, 'actor_role' => $actor?->role?->value ?? $actor?->role, 'note' => $note, 'metadata' => $metadata]);
        return $order->fresh();
    }
}

