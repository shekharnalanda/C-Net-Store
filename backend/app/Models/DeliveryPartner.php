<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPartner extends Model
{
    protected $fillable = ['user_id', 'status', 'vehicle_type', 'vehicle_number', 'driving_licence_number', 'identity_document_path', 'is_online', 'current_latitude', 'current_longitude'];

    protected function casts(): array
    {
        return ['status' => ApprovalStatus::class, 'is_online' => 'boolean', 'current_latitude' => 'decimal:7', 'current_longitude' => 'decimal:7'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

