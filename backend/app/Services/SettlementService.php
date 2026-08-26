<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\SellerSettlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SettlementService
{
    public function generate(Business $business, string $from, string $to): SellerSettlement
    {
        return DB::transaction(function () use ($business, $from, $to): SellerSettlement {
            $orders = Order::query()->where('business_id', $business->id)->where('status', OrderStatus::Delivered)->whereBetween('placed_at', [$from, $to])->whereDoesntHave('settlementItem')->lockForUpdate()->get();
            throw_if($orders->isEmpty(), ValidationException::withMessages(['period' => ['No unsettled delivered orders were found.']]));
            $settlement = SellerSettlement::create(['business_id' => $business->id, 'settlement_number' => 'SET-'.now()->format('ymd').'-'.Str::upper(Str::random(8)), 'period_start' => $from, 'period_end' => $to, 'gross_sales' => 0, 'net_payable' => 0]);
            $totals = ['gross_sales' => 0, 'discount_total' => 0, 'refund_total' => 0, 'commission_total' => 0, 'tax_total' => 0, 'net_payable' => 0];
            foreach ($orders as $order) {
                $gross = (float) $order->subtotal; $discount = (float) $order->discount_total; $tax = (float) $order->tax_total; $refund = (float) $order->refunds()->where('status', 'processed')->sum('amount');
                $commission = round(max(0, $gross - $discount) * ((float) $business->commission_rate / 100), 2);
                $net = round($gross - $discount + $tax - $refund - $commission, 2);
                $settlement->items()->create(['order_id' => $order->id, 'gross_amount' => $gross, 'discount_amount' => $discount, 'refund_amount' => $refund, 'commission_rate' => $business->commission_rate, 'commission_amount' => $commission, 'tax_amount' => $tax, 'net_amount' => $net]);
                foreach ($totals as $key => $_) $totals[$key] += match ($key) { 'gross_sales' => $gross, 'discount_total' => $discount, 'refund_total' => $refund, 'commission_total' => $commission, 'tax_total' => $tax, 'net_payable' => $net };
            }
            $settlement->update($totals);
            return $settlement->load('items');
        });
    }
}

