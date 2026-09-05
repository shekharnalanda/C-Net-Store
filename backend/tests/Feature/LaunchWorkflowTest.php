<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Business;
use App\Models\Cart;
use App\Models\Category;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LaunchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_payment_pending_order_and_reserves_stock(): void
    {
        $fixture = $this->commerceFixture();
        Sanctum::actingAs($fixture['customer']);

        $response = $this->postJson('/api/v1/customer/checkout', [
            'cart_id' => $fixture['cart']->id,
            'address_id' => $fixture['address']->id,
            'outlet_id' => $fixture['outlet']->id,
            'payment_method' => 'upi',
            'fulfilment_type' => 'cnet_delivery',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'payment_pending')
            ->assertJsonPath('data.subtotal', '398.00')
            ->assertJsonPath('data.tax_total', '19.90')
            ->assertJsonPath('data.grand_total', '417.90');

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $orderId,
            'provider' => 'razorpay',
            'status' => 'created',
            'amount' => 417.90,
        ]);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $orderId,
            'inventory_id' => $fixture['inventory']->id,
            'quantity' => 2,
            'status' => 'reserved',
        ]);
        $this->assertDatabaseHas('inventory', [
            'id' => $fixture['inventory']->id,
            'quantity' => 10,
            'reserved_quantity' => 2,
        ]);
        $this->assertDatabaseHas('carts', ['id' => $fixture['cart']->id, 'status' => 'checked_out']);
    }

    public function test_customer_cannot_start_payment_for_another_customers_order(): void
    {
        $fixture = $this->commerceFixture();
        Sanctum::actingAs($fixture['customer']);

        $orderId = $this->postJson('/api/v1/customer/checkout', [
            'cart_id' => $fixture['cart']->id,
            'address_id' => $fixture['address']->id,
            'outlet_id' => $fixture['outlet']->id,
            'payment_method' => 'card',
            'fulfilment_type' => 'seller_delivery',
        ])->assertCreated()->json('data.id');

        $other = $this->user('Other Customer', '9000000099', 'customer');
        Sanctum::actingAs($other);

        $this->postJson("/api/v1/customer/orders/{$orderId}/payment")
            ->assertForbidden();
    }

    public function test_invalid_order_status_transition_is_rejected(): void
    {
        $fixture = $this->commerceFixture();
        $order = $this->order($fixture, OrderStatus::Confirmed);

        $this->expectException(ValidationException::class);
        app(OrderStatusService::class)->transition($order, OrderStatus::Delivered, $fixture['seller']);
    }

    public function test_delivery_partner_cannot_pick_up_with_invalid_otp(): void
    {
        $fixture = $this->commerceFixture();
        $order = $this->order($fixture, OrderStatus::ReadyForPickup);
        $admin = $this->user('Admin User', '9000000003', 'admin');
        $deliveryUser = $this->user('Delivery User', '9000000004', 'delivery_partner');
        $partner = DeliveryPartner::query()->create([
            'user_id' => $deliveryUser->id,
            'status' => 'approved',
            'vehicle_type' => 'bike',
            'is_online' => true,
        ]);
        $assignment = DeliveryAssignment::query()->create([
            'order_id' => $order->id,
            'delivery_partner_id' => $partner->id,
            'assigned_by' => $admin->id,
            'status' => 'assigned',
            'pickup_otp' => '123456',
            'delivery_otp' => '654321',
            'assigned_at' => now(),
        ]);

        Sanctum::actingAs($deliveryUser);

        $this->patchJson("/api/v1/delivery/assignments/{$assignment->id}", [
            'status' => 'picked_up',
            'pickup_otp' => '000000',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('delivery_assignments', [
            'id' => $assignment->id,
            'status' => 'assigned',
            'picked_up_at' => null,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'ready_for_pickup',
        ]);
    }

    private function commerceFixture(): array
    {
        $customer = $this->user('Test Customer', '9000000001', 'customer');
        $seller = $this->user('Test Seller', '9000000002', 'seller');
        $business = Business::query()->create([
            'owner_id' => $seller->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'type' => 'retail',
            'status' => 'approved',
            'phone' => '8000000001',
        ]);
        $outlet = Outlet::query()->create([
            'business_id' => $business->id,
            'name' => 'Main Outlet',
            'phone' => '8000000002',
            'address_line' => 'Test Road',
            'city' => 'Bihar Sharif',
            'postal_code' => '803101',
            'latitude' => 25.2000,
            'longitude' => 85.5200,
            'status' => 'approved',
        ]);
        $category = Category::query()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'marketplace' => 'shopping',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'product_type' => 'shopping',
            'price' => 199,
            'tax_rate' => 5,
            'stock_quantity' => 10,
            'is_active' => true,
        ]);
        $inventory = Inventory::query()->create([
            'outlet_id' => $outlet->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'reserved_quantity' => 0,
            'low_stock_threshold' => 2,
        ]);
        $address = Address::query()->create([
            'user_id' => $customer->id,
            'label' => 'Home',
            'contact_name' => $customer->name,
            'contact_phone' => $customer->phone,
            'address_line' => 'Customer Road',
            'city' => 'Bihar Sharif',
            'postal_code' => '803101',
            'latitude' => 25.2100,
            'longitude' => 85.5100,
            'is_default' => true,
        ]);
        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'business_id' => $business->id,
            'status' => 'active',
        ]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        return compact('customer', 'seller', 'business', 'outlet', 'product', 'inventory', 'address', 'cart');
    }

    private function order(array $fixture, OrderStatus $status): Order
    {
        return Order::query()->create([
            'order_number' => 'TEST-'.strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $fixture['customer']->id,
            'business_id' => $fixture['business']->id,
            'outlet_id' => $fixture['outlet']->id,
            'address_id' => $fixture['address']->id,
            'status' => $status,
            'fulfilment_type' => 'cnet_delivery',
            'subtotal' => 199,
            'grand_total' => 199,
        ]);
    }

    private function user(string $name, string $phone, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'phone' => $phone,
            'password' => 'test-password',
            'role' => $role,
            'status' => 'approved',
        ]);
    }
}
