<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    protected $fillable = ['business_id', 'name', 'phone', 'address_line', 'landmark', 'city', 'postal_code', 'latitude', 'longitude', 'service_radius_km', 'minimum_order', 'status', 'opens_at', 'closes_at'];

    protected function casts(): array
    {
        return ['status' => ApprovalStatus::class, 'latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'service_radius_km' => 'decimal:2', 'minimum_order' => 'decimal:2'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

