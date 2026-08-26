<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => Order::query()->whereHas('business', fn ($query) => $query->where('owner_id', $request->user()->id))->with(['items', 'outlet'])->latest()->paginate(30)]);
    }

    public function update(Request $request, Order $order, OrderStatusService $service): JsonResponse
    {
        abort_unless($order->business->owner_id === $request->user()->id, 403);
        $data = $request->validate(['status' => ['required', Rule::in(['accepted', 'preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered', 'cancelled'])], 'note' => ['nullable', 'string', 'max:500']]);
        if (in_array($data['status'], ['out_for_delivery', 'delivered'], true)) abort_unless($order->fulfilment_type === 'seller_delivery', 422, 'C-Net delivery orders are updated by the assigned partner.');
        return response()->json(['data' => $service->transition($order, OrderStatus::from($data['status']), $request->user(), $data['note'] ?? null)]);
    }
}

