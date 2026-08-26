<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->user_id === $request->user()->id && $order->status === OrderStatus::Delivered, 422, 'Only delivered products can be reviewed.');
        $data = $request->validate(['product_id' => ['required', 'exists:products,id'], 'rating' => ['required', 'integer', 'between:1,5'], 'title' => ['nullable', 'string', 'max:120'], 'comment' => ['nullable', 'string', 'max:2000']]);
        abort_unless($order->items()->where('product_id', $data['product_id'])->exists(), 422, 'This product is not part of the order.');
        $review = Review::updateOrCreate(['user_id' => $request->user()->id, 'order_id' => $order->id, 'product_id' => $data['product_id']], [...$data, 'status' => 'published']);
        return response()->json(['data' => $review], 201);
    }
}

