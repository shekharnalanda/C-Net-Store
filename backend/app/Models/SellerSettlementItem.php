<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerSettlementItem extends Model
{
    protected $fillable = ['seller_settlement_id', 'order_id', 'gross_amount', 'discount_amount', 'refund_amount', 'commission_rate', 'commission_amount', 'tax_amount', 'net_amount'];
    protected function casts(): array { return ['gross_amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'refund_amount' => 'decimal:2', 'commission_rate' => 'decimal:2', 'commission_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'net_amount' => 'decimal:2']; }
    public function settlement(): BelongsTo { return $this->belongsTo(SellerSettlement::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}

