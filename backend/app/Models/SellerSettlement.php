<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerSettlement extends Model
{
    protected $fillable = ['business_id', 'settlement_number', 'period_start', 'period_end', 'gross_sales', 'discount_total', 'refund_total', 'commission_total', 'tax_total', 'net_payable', 'status', 'approved_by', 'approved_at', 'paid_at', 'payment_reference'];
    protected function casts(): array { return ['period_start' => 'date', 'period_end' => 'date', 'gross_sales' => 'decimal:2', 'discount_total' => 'decimal:2', 'refund_total' => 'decimal:2', 'commission_total' => 'decimal:2', 'tax_total' => 'decimal:2', 'net_payable' => 'decimal:2', 'approved_at' => 'datetime', 'paid_at' => 'datetime']; }
    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function items(): HasMany { return $this->hasMany(SellerSettlementItem::class); }
}

