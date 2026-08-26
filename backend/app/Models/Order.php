<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['order_number', 'user_id', 'business_id', 'outlet_id', 'address_id', 'coupon_id', 'coupon_code', 'status', 'fulfilment_type', 'subtotal', 'discount_total', 'tax_total', 'delivery_fee', 'platform_fee', 'grand_total', 'currency', 'customer_note', 'placed_at', 'cancellation_reason', 'cancelled_at'];

    protected function casts(): array
    {
        return ['status' => OrderStatus::class, 'subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'delivery_fee' => 'decimal:2', 'platform_fee' => 'decimal:2', 'grand_total' => 'decimal:2', 'placed_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function address(): BelongsTo { return $this->belongsTo(Address::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function payments(): HasMany { return $this->hasMany(PaymentTransaction::class); }
    public function inventoryReservations(): HasMany { return $this->hasMany(InventoryReservation::class); }
    public function statusHistory(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function deliveryAssignment(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(DeliveryAssignment::class); }
    public function refunds(): HasMany { return $this->hasMany(Refund::class); }
    public function settlementItem(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(SellerSettlementItem::class); }
}
