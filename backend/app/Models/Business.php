<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\BusinessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = ['owner_id', 'name', 'slug', 'type', 'status', 'phone', 'email', 'tax_number', 'commission_rate', 'seller_delivery_enabled', 'cnet_delivery_enabled'];

    protected function casts(): array
    {
        return [
            'type' => BusinessType::class,
            'status' => ApprovalStatus::class,
            'commission_rate' => 'decimal:2',
            'seller_delivery_enabled' => 'boolean',
            'cnet_delivery_enabled' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

