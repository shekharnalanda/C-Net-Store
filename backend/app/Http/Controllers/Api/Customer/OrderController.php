<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'business:id,name',
                'items.product:id,name',
                'payments' => fn ($query) => $query
                    ->select(['id', 'order_id', 'status', 'provider_payment_id', 'amount', 'currency', 'paid_at'])
                    ->latest('id'),
            ])
            ->latest('placed_at')
            ->latest('id')
            ->paginate(20);

        return response()->json($orders);
    }
}
