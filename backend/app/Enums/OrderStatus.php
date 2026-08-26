<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PaymentPending = 'payment_pending';
    case Confirmed = 'confirmed';
    case Accepted = 'accepted';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
}

