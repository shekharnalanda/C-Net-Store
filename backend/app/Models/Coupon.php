<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['business_id', 'code', 'discount_type', 'discount_value', 'maximum_discount', 'minimum_order', 'usage_limit', 'per_user_limit', 'starts_at', 'ends_at', 'is_active'];
    protected function casts(): array { return ['discount_value' => 'decimal:2', 'maximum_discount' => 'decimal:2', 'minimum_order' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_active' => 'boolean']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function redemptions(): HasMany { return $this->hasMany(CouponRedemption::class); }
}

