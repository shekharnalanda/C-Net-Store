<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function calculate(string $code, User $user, int $businessId, float $subtotal): array
    {
        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->where('is_active', true)->where(fn ($query) => $query->whereNull('business_id')->orWhere('business_id', $businessId))->first();
        throw_unless($coupon && now()->between($coupon->starts_at, $coupon->ends_at), ValidationException::withMessages(['coupon_code' => ['Coupon is invalid or expired.']]));
        throw_if($subtotal < (float) $coupon->minimum_order, ValidationException::withMessages(['coupon_code' => ['Minimum order value is not met.']]));
        $validRedemptions = fn ($query) => $query->whereHas('order', fn ($order) => $order->where('status', '!=', 'cancelled'));
        throw_if($coupon->usage_limit !== null && $coupon->redemptions()->where($validRedemptions)->count() >= $coupon->usage_limit, ValidationException::withMessages(['coupon_code' => ['Coupon usage limit has been reached.']]));
        throw_if($coupon->redemptions()->where('user_id', $user->id)->where($validRedemptions)->count() >= $coupon->per_user_limit, ValidationException::withMessages(['coupon_code' => ['You have already used this coupon.']]));

        $discount = $coupon->discount_type === 'percentage' ? $subtotal * ((float) $coupon->discount_value / 100) : (float) $coupon->discount_value;
        if ($coupon->maximum_discount !== null) $discount = min($discount, (float) $coupon->maximum_discount);
        return ['coupon' => $coupon, 'discount' => round(min($discount, $subtotal), 2)];
    }
}
