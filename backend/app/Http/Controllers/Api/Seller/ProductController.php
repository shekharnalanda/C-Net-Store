<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
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
        $products = Product::query()
            ->whereHas(
                'business',
                fn ($query) => $query->where(
                    'owner_id',
                    $request->user()->id
                )
            )
            ->with([
                'business:id,name,status',
                'category:id,name,marketplace,is_active',
                'inventory',
                'libraryImage',
            ])
            ->latest()
            ->paginate(30);

        return response()->json(['data' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $business = Business::query()
            ->where('owner_id', $request->user()->id)
            ->findOrFail($data['business_id']);

        abort_unless(
            $business->status === ApprovalStatus::Approved,
            422,
            'Business approval is required before adding products.'
        );

        $this->validateCategoryAndImage($data);

        $uploadedImage = $data['image'] ?? null;
        unset($data['image']);

        if ($uploadedImage) {
            $data['image_path'] = $uploadedImage->store(
                'products/'.$business->id,
                'public'
            );
            $data['product_image_asset_id'] = null;
        }

        abort_if(
            empty($data['product_image_asset_id'])
                && empty($data['image_path']),
            422,
            'Select a library image or upload a product image.'
        );

        $product = $business->products()->create([
            ...$data,
            'slug' => Str::slug($data['name']).
                '-'.Str::lower(Str::random(6)),
            'is_active' => false,
        ]);

        $this->adjustUsage(null, $product->product_image_asset_id);

        return response()->json([
            'message' => 'Product created and sent for admin review.',
            'data' => $product->load([
                'category',
                'libraryImage',
            ]),
        ], 201);
    }

    public function update(
        Request $request,
        Product $product
    ): JsonResponse {
        abort_unless(
            $product->business->owner_id === $request->user()->id,
            403
        );

        $data = $this->validated($request, true, $product);
        unset($data['business_id']);

        $effectiveData = array_merge(
            [
                'category_id' => $product->category_id,
                'product_type' => $product->product_type,
                'product_image_asset_id' =>
                    $product->product_image_asset_id,
            ],
            $data
        );

        $this->validateCategoryAndImage($effectiveData);

        $oldAssetId = $product->product_image_asset_id;
        $uploadedImage = $data['image'] ?? null;
        unset($data['image']);

        if ($uploadedImage) {
            $data['image_path'] = $uploadedImage->store(
                'products/'.$product->business_id,
                'public'
            );
            $data['product_image_asset_id'] = null;
        } elseif (
            array_key_exists('product_image_asset_id', $data)
            && $data['product_image_asset_id']
        ) {
            $data['image_path'] = null;
        }

        $futureLibraryImage = array_key_exists(
            'product_image_asset_id',
            $data
        )
            ? $data['product_image_asset_id']
            : $product->product_image_asset_id;

        $futureCustomImage = array_key_exists('image_path', $data)
            ? $data['image_path']
            : $product->image_path;

        abort_if(
            empty($futureLibraryImage) && empty($futureCustomImage),
            422,
            'Select a library image or upload a product image.'
        );

        $product->update([
            ...$data,
            'is_active' => false,
        ]);

        $this->adjustUsage(
            $oldAssetId,
            $product->product_image_asset_id
        );

        return response()->json([
            'message' => 'Product updated and sent for admin review.',
            'data' => $product->fresh()->load([
                'category',
                'libraryImage',
            ]),
        ]);
    }

    private function validated(
        Request $request,
        bool $updating = false,
        ?Product $product = null
    ): array {
        $required = $updating ? 'sometimes' : 'required';
        $businessId = $request->input(
            'business_id',
            $product?->business_id
        );

        return $request->validate([
            'business_id' => [
                $required,
                'integer',
                'exists:businesses,id',
            ],
            'category_id' => [
                $required,
                'integer',
                Rule::exists('categories', 'id')
                    ->where('is_active', true),
            ],
            'product_image_asset_id' => [
                'nullable',
                'integer',
                Rule::exists('product_image_assets', 'id')
                    ->where('is_active', true),
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120',
            ],
            'name' => [$required, 'string', 'max:190'],
            'sku' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('products', 'sku')
                    ->where(
                        fn ($query) => $query->where(
                            'business_id',
                            $businessId
                        )
                    )
                    ->ignore($product?->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'product_type' => [
                $required,
                Rule::in(['shopping', 'grocery', 'food']),
            ],
            'price' => [$required, 'numeric', 'min:0.01'],
            'sale_price' => [
                'nullable',
                'numeric',
                'min:0.01',
                'lte:price',
            ],
            'tax_rate' => [
                'nullable',
                'numeric',
                'between:0,100',
            ],
            'stock_quantity' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'unit' => ['nullable', 'string', 'max:30'],
            'preparation_minutes' => [
                'nullable',
                'integer',
                'between:0,600',
            ],
            'is_vegetarian' => ['nullable', 'boolean'],
        ]);
    }

    private function validateCategoryAndImage(array $data): void
    {
        $category = Category::query()
            ->where('is_active', true)
            ->findOrFail($data['category_id']);

        abort_unless(
            $category->marketplace === $data['product_type'],
            422,
            'Selected category does not match the product type.'
        );

        $assetId = $data['product_image_asset_id'] ?? null;

        if ($assetId) {
            $validAsset = ProductImageAsset::query()
                ->whereKey($assetId)
                ->where('is_active', true)
                ->where('category_id', $category->id)
                ->exists();

            abort_unless(
                $validAsset,
                422,
                'Selected image does not belong to this category.'
            );
        }
    }

    private function adjustUsage(
        ?int $oldAssetId,
        ?int $newAssetId
    ): void {
        if ($oldAssetId === $newAssetId) {
            return;
        }

        if ($oldAssetId) {
            ProductImageAsset::query()
                ->whereKey($oldAssetId)
                ->where('usage_count', '>', 0)
                ->decrement('usage_count');
        }

        if ($newAssetId) {
            ProductImageAsset::query()
                ->whereKey($newAssetId)
                ->increment('usage_count');
        }
    }
}
