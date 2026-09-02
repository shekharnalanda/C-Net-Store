<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_public_and_has_a_stable_empty_shape(): void
    {
        $this->getJson('/api/v1/customer/catalog')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonCount(0, 'categories')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_catalog_only_returns_approved_active_inventory(): void
    {
        $shopping = $this->category('Electronics', 'shopping');
        $hiddenCategory = $this->category('Hidden', 'shopping', false);
        $approved = $this->business('approved', 1);
        $pending = $this->business('pending', 2);

        $this->product($approved, $shopping, 'Visible Phone', true);
        $this->product($approved, $shopping, 'Inactive Phone', false);
        $this->product($pending, $shopping, 'Pending Seller Phone', true);
        $this->product($approved, $hiddenCategory, 'Hidden Category Phone', true);

        $response = $this->getJson('/api/v1/customer/catalog');

        $response->assertOk()->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Visible Phone');
        $response->assertJsonPath('meta.total', 1);
    }

    public function test_catalog_filters_products_and_categories(): void
    {
        $shopping = $this->category('Electronics', 'shopping');
        $grocery = $this->category('Grocery', 'grocery');
        $business = $this->business('approved', 3);
        $this->product($business, $shopping, 'Smart Phone', true, 'shopping');
        $this->product($business, $grocery, 'Basmati Rice', true, 'grocery');

        $this->getJson('/api/v1/customer/catalog?type=grocery&q=Rice')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('data.0.name', 'Basmati Rice')
            ->assertJsonPath('categories.0.name', 'Grocery');

        $this->getJson('/api/v1/customer/catalog?category_id='.$shopping->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Smart Phone');
    }

    private function category(string $name, string $marketplace, bool $active = true): Category
    {
        return Category::query()->create([
            'name' => $name,
            'slug' => strtolower($name).'-'.uniqid(),
            'marketplace' => $marketplace,
            'sort_order' => 1,
            'is_active' => $active,
        ]);
    }

    private function business(string $status, int $sequence): Business
    {
        $user = User::query()->create([
            'name' => 'Seller '.$sequence,
            'email' => 'seller'.$sequence.'@example.com',
            'phone' => '900000000'.$sequence,
            'password' => 'test-password',
            'role' => 'seller',
            'status' => 'approved',
            'preferred_language' => 'en',
        ]);

        return Business::query()->create([
            'owner_id' => $user->id,
            'name' => 'Business '.$sequence,
            'slug' => 'business-'.$sequence,
            'type' => 'retail',
            'status' => $status,
            'phone' => '800000000'.$sequence,
        ]);
    }

    private function product(
        Business $business,
        Category $category,
        string $name,
        bool $active,
        string $type = 'shopping',
    ): Product {
        return Product::query()->create([
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
            'product_type' => $type,
            'price' => 199,
            'stock_quantity' => 10,
            'is_active' => $active,
        ]);
    }
}
