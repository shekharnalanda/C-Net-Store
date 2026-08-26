<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryEarning extends Model
{
    protected $fillable = ['delivery_assignment_id', 'delivery_partner_id', 'base_amount', 'distance_amount', 'incentive_amount', 'deduction_amount', 'net_amount', 'status', 'settled_at'];
    protected function casts(): array { return ['base_amount' => 'decimal:2', 'distance_amount' => 'decimal:2', 'incentive_amount' => 'decimal:2', 'deduction_amount' => 'decimal:2', 'net_amount' => 'decimal:2', 'settled_at' => 'datetime']; }
    public function assignment(): BelongsTo { return $this->belongsTo(DeliveryAssignment::class); }
    public function partner(): BelongsTo { return $this->belongsTo(DeliveryPartner::class, 'delivery_partner_id'); }
}

