<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImageAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_category_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/seller/product-categories')->assertUnauthorized();
    }

    public function test_seller_category_endpoint_only_returns_active_matching_categories(): void
    {
        Sanctum::actingAs($this->user());

        Category::query()->create($this->categoryData('Shopping', 'shopping', true));
        Category::query()->create($this->categoryData('Grocery', 'grocery', true));
        Category::query()->create($this->categoryData('Hidden', 'shopping', false));

        $response = $this->getJson('/api/v1/seller/product-categories?product_type=shopping');

        $response->assertOk()->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Shopping');
    }

    public function test_unapproved_business_cannot_submit_products(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);
        $business = $this->business($user, 'pending');
        $category = Category::query()->create($this->categoryData('Shopping', 'shopping'));
        $asset = $this->asset($category);

        $this->postJson('/api/v1/seller/products', $this->productData($business, $category, $asset))
            ->assertStatus(422);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_category_and_library_image_must_match_product_type_and_category(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);
        $business = $this->business($user, 'approved');
        $shopping = Category::query()->create($this->categoryData('Shopping', 'shopping'));
        $grocery = Category::query()->create($this->categoryData('Grocery', 'grocery'));
        $shoppingAsset = $this->asset($shopping);
        $groceryAsset = $this->asset($grocery);

        $this->postJson('/api/v1/seller/products', $this->productData($business, $grocery, $shoppingAsset))
            ->assertStatus(422);

        $this->postJson('/api/v1/seller/products', $this->productData($business, $shopping, $groceryAsset))
            ->assertStatus(422);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_valid_submission_is_inactive_updates_usage_and_edit_resets_review(): void
    {
        $user = $this->user();
        Sanctum::actingAs($user);
        $business = $this->business($user, 'approved');
        $category = Category::query()->create($this->categoryData('Shopping', 'shopping'));
        $asset = $this->asset($category);

        $response = $this->postJson(
            '/api/v1/seller/products',
            $this->productData($business, $category, $asset)
        );

        $response->assertCreated()->assertJsonPath('data.is_active', false);
        $product = Product::query()->firstOrFail();
        $this->assertFalse($product->is_active);
        $this->assertSame(1, $asset->fresh()->usage_count);

        $product->forceFill(['is_active' => true])->save();
        $update = $this->productData($business, $category, $asset);
        $update['name'] = 'Updated Test Product';

        $this->putJson('/api/v1/seller/products/'.$product->id, $update)
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertFalse($product->fresh()->is_active);
        $this->assertSame(1, $asset->fresh()->usage_count);
    }

    private function categoryData(string $name, string $marketplace, bool $active = true): array
    {
        return [
            'name' => $name,
            'slug' => strtolower($name).'-'.uniqid(),
            'marketplace' => $marketplace,
            'sort_order' => 1,
            'is_active' => $active,
        ];
    }

    private function business(User $user, string $status): Business
    {
        return Business::query()->create([
            'owner_id' => $user->id,
            'name' => 'Test Business '.uniqid(),
            'slug' => 'test-business-'.uniqid(),
            'type' => 'shopping',
            'status' => $status,
        ]);
    }

    private function user(): User
    {
        $token = uniqid();

        return User::query()->create([
            'name' => 'Test Seller',
            'email' => 'seller-'.$token.'@example.com',
            'phone' => '9'.substr(preg_replace('/\D/', '', $token), -9),
            'password' => 'test-password',
            'role' => 'seller',
            'status' => 'approved',
            'preferred_language' => 'en',
        ]);
    }

    private function asset(Category $category): ProductImageAsset
    {
        $token = uniqid();

        return ProductImageAsset::query()->create([
            'category_id' => $category->id,
            'group_name' => $category->name,
            'name' => 'Test Asset '.$token,
            'source' => 'test',
            'slug' => 'test-asset-'.$token,
            'keywords' => ['test', 'परीक्षण'],
            'image_path' => 'product-image-library/test-'.$token.'.webp',
            'alt_text' => 'Test product image',
            'license_type' => 'owned',
            'license_source' => 'C-Net Store',
            'is_active' => true,
            'sort_order' => 1,
            'usage_count' => 0,
        ]);
    }

    private function productData(Business $business, Category $category, ProductImageAsset $asset): array
    {
        return [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'product_image_asset_id' => $asset->id,
            'name' => 'Test Product',
            'sku' => 'TEST-'.uniqid(),
            'product_type' => 'shopping',
            'price' => 199.00,
            'sale_price' => 149.00,
            'stock_quantity' => 10,
            'unit' => 'piece',
        ];
    }
}
