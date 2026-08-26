<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookEvent;
use App\Services\InventoryReservationService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $gateway, InventoryReservationService $inventory): JsonResponse
    {
        $raw = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');
        abort_unless($signature && $gateway->verifyWebhookSignature($raw, $signature), 401, 'Invalid webhook signature.');
        $payload = $request->json()->all();
        $eventId = (string) ($request->header('X-Razorpay-Event-Id') ?: hash('sha256', $signature.$raw));

        $event = PaymentWebhookEvent::firstOrCreate(['provider' => 'razorpay', 'event_id' => $eventId], ['event_type' => $payload['event'] ?? 'unknown', 'signature' => $signature, 'payload' => $payload]);
        if ($event->processed_at) return response()->json(['status' => 'already_processed']);

        DB::transaction(function () use ($payload, $event, $inventory): void {
            $entity = data_get($payload, 'payload.payment.entity', []);
            $providerOrderId = data_get($entity, 'order_id');
            $transaction = PaymentTransaction::query()->where('provider_order_id', $providerOrderId)->lockForUpdate()->first();
            if (! $transaction) { $event->update(['processed_at' => now(), 'processing_error' => 'Transaction not found']); return; }
            $order = Order::query()->lockForUpdate()->findOrFail($transaction->order_id);
            $type = (string) ($payload['event'] ?? '');

            if (in_array($type, ['payment.captured', 'order.paid'], true) && $order->status === OrderStatus::PaymentPending) {
                $inventory->commit($order);
                $transaction->update(['provider_payment_id' => data_get($entity, 'id'), 'status' => PaymentStatus::Captured, 'paid_at' => now(), 'provider_payload' => $entity]);
                $order->update(['status' => OrderStatus::Confirmed, 'placed_at' => now()]);
            } elseif ($type === 'payment.failed' && $order->status === OrderStatus::PaymentPending) {
                $inventory->release($order);
                $transaction->update(['status' => PaymentStatus::Failed, 'failure_code' => data_get($entity, 'error_code'), 'failure_message' => Str::limit((string) data_get($entity, 'error_description'), 1000), 'provider_payload' => $entity]);
                $order->update(['status' => OrderStatus::Cancelled]);
            }
            $event->update(['processed_at' => now()]);
        });

        return response()->json(['status' => 'ok']);
    }
}

