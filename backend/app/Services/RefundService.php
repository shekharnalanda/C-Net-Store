<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function request(Order $order, User $actor, string $reason): Refund
    {
        $payment = $order->payments()->where('status', PaymentStatus::Captured)->latest()->first();
        throw_unless($payment?->provider_payment_id, ValidationException::withMessages(['order' => ['No captured online payment is available for refund.']]));
        throw_if($order->refunds()->whereIn('status', ['requested', 'processing', 'processed'])->exists(), ValidationException::withMessages(['order' => ['A refund already exists for this order.']]));

        $refund = Refund::create(['order_id' => $order->id, 'payment_transaction_id' => $payment->id, 'requested_by' => $actor->id, 'amount' => $payment->amount, 'reason' => $reason, 'status' => 'processing', 'requested_at' => now()]);
        $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))->acceptJson()->timeout(15)->post("https://api.razorpay.com/v1/payments/{$payment->provider_payment_id}/refund", ['amount' => (int) round((float) $refund->amount * 100), 'speed' => 'normal', 'notes' => ['cnet_order_id' => (string) $order->id]]);
        if (! $response->successful()) { $refund->update(['status' => 'failed', 'provider_payload' => $response->json()]); throw ValidationException::withMessages(['refund' => ['Refund could not be submitted.']]); }
        $payload = $response->json();

        return DB::transaction(function () use ($order, $payment, $refund, $payload): Refund {
            $refund->update(['provider_refund_id' => $payload['id'], 'status' => $payload['status'] === 'processed' ? 'processed' : 'processing', 'provider_payload' => $payload, 'processed_at' => $payload['status'] === 'processed' ? now() : null]);
            $processed = $payload['status'] === 'processed';
            $order->update(['status' => $processed ? OrderStatus::Refunded : OrderStatus::Cancelled, 'cancellation_reason' => $refund->reason, 'cancelled_at' => now()]);
            if ($processed) $payment->update(['status' => PaymentStatus::Refunded]);
            return $refund->fresh();
        });
    }
}
