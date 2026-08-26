<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = ['order_id', 'payment_transaction_id', 'requested_by', 'approved_by', 'provider_refund_id', 'amount', 'reason', 'status', 'provider_payload', 'requested_at', 'processed_at'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'provider_payload' => 'encrypted:array', 'requested_at' => 'datetime', 'processed_at' => 'datetime']; }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function payment(): BelongsTo { return $this->belongsTo(PaymentTransaction::class, 'payment_transaction_id'); }
}

