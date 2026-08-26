<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case AtPickup = 'at_pickup';
    case PickedUp = 'picked_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}

