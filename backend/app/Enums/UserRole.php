<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Seller = 'seller';
    case DeliveryPartner = 'delivery_partner';
    case Staff = 'staff';
    case SuperAdmin = 'super_admin';
}

