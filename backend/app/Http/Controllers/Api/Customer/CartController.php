<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request, Cart $cart): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id && $cart->status === 'active', 403);
        return response()->json(['data' => $cart->load('items.product')]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $data = $request->validate(['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'integer', 'between:1,99']]);
        $product = Product::query()->where('is_active', true)->findOrFail($data['product_id']);
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id, 'business_id' => $product->business_id, 'status' => 'active']);
        $item = $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $data['quantity']]);
        return response()->json(['data' => $item->load('product')], 201);
    }

    public function removeItem(Request $request, Cart $cart, int $item): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);
        $cart->items()->whereKey($item)->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}

