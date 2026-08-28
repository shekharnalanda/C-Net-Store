<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductImageAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => Product::query()->whereHas('business', fn ($query) => $query->where('owner_id', $request->user()->id))->with(['category', 'inventory', 'libraryImage'])->paginate(30)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $business = Business::query()->where('owner_id', $request->user()->id)->findOrFail($data['business_id']);
        abort_unless($business->status === ApprovalStatus::Approved, 422, 'Business approval is required before adding products.');

        $product = $business->products()->create([
            ...$data,
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
            'is_active' => false,
        ]);

        if ($product->product_image_asset_id) {
            ProductImageAsset::whereKey($product->product_image_asset_id)->increment('usage_count');
        }

        return response()->json(['message' => 'Product created for review.', 'data' => $product], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->business->owner_id === $request->user()->id, 403);
        $data = $this->validated($request, true);
        unset($data['business_id']);
        $oldAssetId = $product->product_image_asset_id;
        $product->update([...$data, 'is_active' => false]);
        if ($oldAssetId !== $product->product_image_asset_id) {
            ProductImageAsset::whereKey($oldAssetId)->where('usage_count', '>', 0)->decrement('usage_count');
            ProductImageAsset::whereKey($product->product_image_asset_id)->increment('usage_count');
        }

        return response()->json(['message' => 'Product updated and sent for review.', 'data' => $product->fresh()]);
    }

    private function validated(Request $request, bool $updating = false): array
    {
        $required = $updating ? 'sometimes' : 'required';

        return $request->validate([
            'business_id' => [$required, 'integer', 'exists:businesses,id'],
            'category_id' => [$required, 'integer', 'exists:categories,id'],
            'product_image_asset_id' => ['nullable', 'integer', Rule::exists('product_image_assets', 'id')->where('is_active', true)],
            'name' => [$required, 'string', 'max:190'],
            'sku' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:5000'],
            'product_type' => [$required, Rule::in(['shopping', 'grocery', 'food'])],
            'price' => [$required, 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:30'],
            'preparation_minutes' => ['nullable', 'integer', 'between:0,600'],
            'is_vegetarian' => ['nullable', 'boolean'],
        ]);
    }
}
