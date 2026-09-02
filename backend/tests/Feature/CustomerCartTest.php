<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_add_update_and_remove_cart_items(): void
    {
        [$customer, $product] = $this->customerAndProduct();
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/customer/carts')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $added = $this->postJson('/api/v1/customer/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $cartId = $added->json('data.cart_id');
        $itemId = $added->json('data.id');

        $this->getJson('/api/v1/customer/carts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.items.0.product.name', 'Test Product');

        $this->patchJson("/api/v1/customer/carts/{$cartId}/items/{$itemId}", [
            'quantity' => 3,
        ])->assertOk()->assertJsonPath('data.quantity', 3);

        $this->deleteJson("/api/v1/customer/carts/{$cartId}/items/{$itemId}")
            ->assertOk();

        $this->getJson('/api/v1/customer/carts')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_customer_cannot_change_another_customers_cart(): void
    {
        [$owner, $product] = $this->customerAndProduct();
        $other = User::query()->create([
            'name' => 'Other Customer',
            'phone' => '9000000099',
            'password' => 'test-password',
            'role' => 'customer',
            'status' => 'approved',
        ]);
        $cart = Cart::query()->create([
            'user_id' => $owner->id,
            'business_id' => $product->business_id,
            'status' => 'active',
        ]);
        $item = $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
        Sanctum::actingAs($other);

        $this->patchJson("/api/v1/customer/carts/{$cart->id}/items/{$item->id}", [
            'quantity' => 2,
        ])->assertForbidden();
    }

    private function customerAndProduct(): array
    {
        $customer = User::query()->create([
            'name' => 'Test Customer',
            'phone' => '9000000001',
            'password' => 'test-password',
            'role' => 'customer',
            'status' => 'approved',
        ]);
        $seller = User::query()->create([
            'name' => 'Test Seller',
            'phone' => '9000000002',
            'password' => 'test-password',
            'role' => 'seller',
            'status' => 'approved',
        ]);
        $business = Business::query()->create([
            'owner_id' => $seller->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'type' => 'retail',
            'status' => 'approved',
            'phone' => '8000000001',
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
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        return [$customer, $product];
    }
}
