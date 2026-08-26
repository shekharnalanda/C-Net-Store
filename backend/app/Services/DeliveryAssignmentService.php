<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentService
{
    public function assign(Order $order, DeliveryPartner $partner, User $admin): DeliveryAssignment
    {
        throw_unless($order->fulfilment_type === 'cnet_delivery', ValidationException::withMessages(['order' => ['This order uses seller delivery.']]));
        throw_unless($partner->status === ApprovalStatus::Approved, ValidationException::withMessages(['partner' => ['Delivery partner is not approved.']]));
        throw_unless(in_array($order->status, [OrderStatus::Accepted, OrderStatus::Preparing, OrderStatus::ReadyForPickup], true), ValidationException::withMessages(['order' => ['Order is not ready for assignment.']]));

        return DB::transaction(fn () => DeliveryAssignment::create(['order_id' => $order->id, 'delivery_partner_id' => $partner->id, 'assigned_by' => $admin->id, 'status' => DeliveryStatus::Assigned, 'pickup_otp' => (string) random_int(100000, 999999), 'delivery_otp' => (string) random_int(100000, 999999), 'assigned_at' => now()]));
    }
}

