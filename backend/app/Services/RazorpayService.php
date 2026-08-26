<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RazorpayService
{
    public function createProviderOrder(Order $order, PaymentTransaction $transaction): array
    {
        $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
            ->acceptJson()->timeout(15)->retry(2, 250)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round((float) $order->grand_total * 100),
                'currency' => 'INR',
                'receipt' => $order->order_number,
                'notes' => ['cnet_order_id' => (string) $order->id],
            ]);

        if (! $response->successful()) {
            throw ValidationException::withMessages(['payment' => ['Unable to start online payment. Please try again.']]);
        }

        $payload = $response->json();
        $transaction->update(['provider_order_id' => $payload['id'], 'provider_payload' => $payload]);

        return ['key_id' => config('services.razorpay.key_id'), 'provider_order_id' => $payload['id'], 'amount' => $payload['amount'], 'currency' => $payload['currency']];
    }

    public function verifyCheckoutSignature(string $providerOrderId, string $providerPaymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $providerOrderId.'|'.$providerPaymentId, (string) config('services.razorpay.key_secret'));
        return hash_equals($expected, $signature);
    }

    public function verifyWebhookSignature(string $rawPayload, string $signature): bool
    {
        $expected = hash_hmac('sha256', $rawPayload, (string) config('services.razorpay.webhook_secret'));
        return hash_equals($expected, $signature);
    }
}

