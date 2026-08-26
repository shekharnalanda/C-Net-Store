<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly InventoryReservationService $inventory) {}

    public function createOrder(Cart $cart, Address $address, int $outletId, string $fulfilmentType, ?string $note = null): Order
    {
        if (config('store.cod_enabled', false)) {
            throw ValidationException::withMessages(['payment_method' => ['Cash on Delivery is not supported.']]);
        }

        $cart->load(['business', 'items.product']);
        throw_if($cart->business->status !== ApprovalStatus::Approved, ValidationException::withMessages(['cart' => ['This store is not available.']]));
        throw_if($cart->items->isEmpty(), ValidationException::withMessages(['cart' => ['The cart is empty.']]));

        return DB::transaction(function () use ($cart, $address, $outletId, $fulfilmentType, $note): Order {
            $subtotal = 0;
            $taxTotal = 0;
            foreach ($cart->items as $item) {
                $product = $item->product;
                throw_if(! $product->is_active, ValidationException::withMessages(['cart' => ["{$product->name} is unavailable."]]));
                $price = $product->sale_price ?? $product->price;
                $lineSubtotal = round((float) $price * $item->quantity, 2);
                $subtotal += $lineSubtotal;
                $taxTotal += round($lineSubtotal * ((float) $product->tax_rate / 100), 2);
            }

            $deliveryFee = 0.00;
            $platformFee = 0.00;
            $order = Order::create([
                'order_number' => 'CNS-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => $cart->user_id,
                'business_id' => $cart->business_id,
                'outlet_id' => $outletId,
                'address_id' => $address->id,
                'status' => OrderStatus::PaymentPending,
                'fulfilment_type' => $fulfilmentType,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'delivery_fee' => $deliveryFee,
                'platform_fee' => $platformFee,
                'grand_total' => round($subtotal + $taxTotal + $deliveryFee + $platformFee, 2),
                'customer_note' => $note,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $price = (float) ($product->sale_price ?? $product->price);
                $lineSubtotal = round($price * $item->quantity, 2);
                $tax = round($lineSubtotal * ((float) $product->tax_rate / 100), 2);
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'sku' => $product->sku, 'unit_price' => $price, 'quantity' => $item->quantity, 'tax_rate' => $product->tax_rate, 'tax_amount' => $tax, 'line_total' => $lineSubtotal + $tax]);
            }

            $this->inventory->reserve($order);

            $order->payments()->create(['provider' => 'razorpay', 'status' => PaymentStatus::Created, 'amount' => $order->grand_total, 'currency' => 'INR', 'idempotency_key' => (string) Str::uuid()]);
            $cart->update(['status' => 'checked_out']);

            return $order->load(['items', 'payments']);
        });
    }
}
