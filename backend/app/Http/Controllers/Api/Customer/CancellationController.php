<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryReservationService;
use App\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancellationController extends Controller
{
    public function store(Request $request, Order $order, InventoryReservationService $inventory, RefundService $refunds): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, [OrderStatus::PaymentPending, OrderStatus::Confirmed, OrderStatus::Accepted], true), 422, 'This order can no longer be cancelled online.');
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $refund = null;
        if ($order->status === OrderStatus::PaymentPending) DB::transaction(function () use ($order, $inventory, $data): void { $inventory->release($order); $order->update(['status' => OrderStatus::Cancelled, 'cancellation_reason' => $data['reason'], 'cancelled_at' => now()]); });
        else $refund = $refunds->request($order, $request->user(), $data['reason']);
        return response()->json(['message' => $refund ? 'Cancellation and refund submitted.' : 'Order cancelled.', 'refund' => $refund]);
    }
}

