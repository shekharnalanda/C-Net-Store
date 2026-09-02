<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $carts = Cart::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->whereHas('items')
            ->with(['business:id,name,slug', 'items.product.libraryImage'])
            ->latest('updated_at')
            ->get();

        return response()->json(['data' => $carts]);
    }

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

    public function updateItem(Request $request, Cart $cart, int $item): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id && $cart->status === 'active', 403);
        $data = $request->validate(['quantity' => ['required', 'integer', 'between:1,99']]);
        $cartItem = $cart->items()->whereKey($item)->firstOrFail();
        $cartItem->update(['quantity' => $data['quantity']]);

        return response()->json(['data' => $cartItem->load('product')]);
    }

    public function removeItem(Request $request, Cart $cart, int $item): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);
        $cart->items()->whereKey($item)->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}
