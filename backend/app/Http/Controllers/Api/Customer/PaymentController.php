<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\InventoryReservationService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Request $request, Order $order, RazorpayService $gateway): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id && $order->status === OrderStatus::PaymentPending, 403);
        $transaction = $order->payments()->where('status', PaymentStatus::Created)->latest()->firstOrFail();
        return response()->json(['data' => $gateway->createProviderOrder($order, $transaction)]);
    }

    public function verify(Request $request, Order $order, RazorpayService $gateway, InventoryReservationService $inventory): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        $data = $request->validate(['razorpay_order_id' => ['required', 'string'], 'razorpay_payment_id' => ['required', 'string'], 'razorpay_signature' => ['required', 'string']]);
        $transaction = PaymentTransaction::query()->where('order_id', $order->id)->where('provider_order_id', $data['razorpay_order_id'])->firstOrFail();
        abort_unless($gateway->verifyCheckoutSignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']), 422, 'Invalid payment signature.');

        DB::transaction(function () use ($order, $transaction, $data, $inventory): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->status !== OrderStatus::PaymentPending) return;
            $inventory->commit($lockedOrder);
            $transaction->update(['provider_payment_id' => $data['razorpay_payment_id'], 'status' => PaymentStatus::Captured, 'paid_at' => now()]);
            $lockedOrder->update(['status' => OrderStatus::Confirmed, 'placed_at' => now()]);
        });

        return response()->json(['message' => 'Payment verified and order confirmed.', 'data' => $order->fresh()]);
    }
}

