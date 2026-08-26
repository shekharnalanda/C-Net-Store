<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Outlet;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function store(Request $request, CheckoutService $checkout): JsonResponse
    {
        $data = $request->validate(['cart_id' => ['required', 'exists:carts,id'], 'address_id' => ['required', 'exists:addresses,id'], 'outlet_id' => ['required', 'exists:outlets,id'], 'payment_method' => ['required', Rule::in(['upi', 'card', 'netbanking', 'wallet'])], 'fulfilment_type' => ['required', Rule::in(['cnet_delivery', 'seller_delivery'])], 'customer_note' => ['nullable', 'string', 'max:500']]);
        $cart = Cart::query()->where('user_id', $request->user()->id)->where('status', 'active')->findOrFail($data['cart_id']);
        $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($data['address_id']);
        Outlet::query()->where('business_id', $cart->business_id)->where('status', 'approved')->findOrFail($data['outlet_id']);
        $order = $checkout->createOrder($cart, $address, $data['outlet_id'], $data['fulfilment_type'], $data['customer_note'] ?? null);
        return response()->json(['message' => 'Online payment is required to confirm this order.', 'data' => $order], 201);
    }
}

