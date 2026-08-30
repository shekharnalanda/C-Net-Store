<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Outlet;
use App\Services\CentralSyncService;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function store(Request $request, CheckoutService $checkout, CentralSyncService $centralSync): JsonResponse
    {
        $data = $request->validate(['cart_id' => ['required', 'exists:carts,id'], 'address_id' => ['required', 'exists:addresses,id'], 'outlet_id' => ['required', 'exists:outlets,id'], 'payment_method' => ['required', Rule::in(['upi', 'card', 'netbanking', 'wallet'])], 'fulfilment_type' => ['required', Rule::in(['cnet_delivery', 'seller_delivery'])], 'customer_note' => ['nullable', 'string', 'max:500'], 'coupon_code' => ['nullable', 'string', 'max:50']]);
        $cart = Cart::query()->where('user_id', $request->user()->id)->where('status', 'active')->findOrFail($data['cart_id']);
        $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($data['address_id']);
        Outlet::query()->where('business_id', $cart->business_id)->where('status', 'approved')->findOrFail($data['outlet_id']);
        $order = $checkout->createOrder($cart, $address, $data['outlet_id'], $data['fulfilment_type'], $data['customer_note'] ?? null, $data['coupon_code'] ?? null);

        $user = $request->user();
        $centralSync->admission([
            'business_code' => config('services.mci_central.business_code'),
            'source_reference_id' => 'store-order-'.$order->order_number,
            'source_site' => config('app.url', 'https://cnetstore.mciedu.com'),
            'application_reference' => $order->order_number,
            'applicant_name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'course_program' => 'C-Net Store Order',
            'status' => is_object($order->status) ? $order->status->value : (string) $order->status,
            'payment_status' => 'pending',
            'submitted_at' => ($order->placed_at ?: $order->created_at ?: now())->toIso8601String(),
            'metadata' => [
                'order_id' => $order->id,
                'business_id' => $order->business_id,
                'outlet_id' => $order->outlet_id,
                'grand_total' => (string) $order->grand_total,
                'currency' => $order->currency,
                'fulfilment_type' => $order->fulfilment_type,
                'payment_method' => $data['payment_method'],
            ],
        ]);

        return response()->json(['message' => 'Online payment is required to confirm this order.', 'data' => $order], 201);
    }
}
