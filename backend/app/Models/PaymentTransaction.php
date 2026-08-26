<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = ['order_id', 'provider', 'provider_order_id', 'provider_payment_id', 'status', 'amount', 'currency', 'idempotency_key', 'failure_code', 'failure_message', 'paid_at', 'provider_payload'];

    protected function casts(): array
    {
        return ['status' => PaymentStatus::class, 'amount' => 'decimal:2', 'paid_at' => 'datetime', 'provider_payload' => 'encrypted:array'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}

