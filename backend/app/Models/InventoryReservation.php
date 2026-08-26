<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $fillable = ['order_id', 'inventory_id', 'quantity', 'status', 'expires_at', 'released_at', 'committed_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'released_at' => 'datetime', 'committed_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function inventory(): BelongsTo { return $this->belongsTo(Inventory::class); }
}

