<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAssignment extends Model
{
    protected $fillable = ['order_id', 'delivery_partner_id', 'assigned_by', 'status', 'pickup_otp', 'delivery_otp', 'assigned_at', 'accepted_at', 'picked_up_at', 'delivered_at', 'failure_reason'];

    protected $hidden = ['pickup_otp', 'delivery_otp'];

    protected function casts(): array
    {
        return ['status' => DeliveryStatus::class, 'assigned_at' => 'datetime', 'accepted_at' => 'datetime', 'picked_up_at' => 'datetime', 'delivered_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function partner(): BelongsTo { return $this->belongsTo(DeliveryPartner::class, 'delivery_partner_id'); }
    public function assigner(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }
    public function locations(): HasMany { return $this->hasMany(DeliveryLocation::class); }
}

