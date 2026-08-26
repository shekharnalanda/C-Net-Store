<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLocation extends Model
{
    protected $fillable = ['delivery_assignment_id', 'latitude', 'longitude', 'accuracy_meters', 'recorded_at'];

    protected function casts(): array { return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'accuracy_meters' => 'decimal:2', 'recorded_at' => 'datetime']; }
    public function assignment(): BelongsTo { return $this->belongsTo(DeliveryAssignment::class, 'delivery_assignment_id'); }
}

