<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductImageAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductImageLibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'group' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $assets = ProductImageAsset::query()
            ->where('is_active', true)
            ->with('category:id,name,parent_id,marketplace')
            ->when($data['category_id'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($data['group'] ?? null, fn ($query, $group) => $query->where('group_name', $group))
            ->when($data['search'] ?? null, function ($query, $search): void {
                $escaped = addcslashes($search, '%_');
                $query->where(fn ($match) => $match
                    ->where('name', 'like', "%{$escaped}%")
                    ->orWhere('group_name', 'like', "%{$escaped}%")
                    ->orWhere('keywords', 'like', "%{$escaped}%"));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24);

        return response()->json(['data' => $assets]);
    }

    public function categories(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_type' => ['nullable', Rule::in(['shopping', 'grocery', 'food'])],
        ]);

        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->when(
                $data['product_type'] ?? null,
                fn ($query, $type) => $query->where('marketplace', $type)
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'marketplace', 'image_path']);

        return response()->json(['data' => $categories]);
    }

    public function groups(): JsonResponse
    {
        return response()->json(['data' => ProductImageAsset::query()
            ->where('is_active', true)->select('group_name')->distinct()
            ->orderBy('group_name')->pluck('group_name')]);
    }
}
