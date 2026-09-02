<?php

namespace App\Http\Controllers\Api\Customer;

use App\Enums\ApprovalStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:shopping,grocery,food'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->when(isset($filters['type']), fn ($query) => $query->where('marketplace', $filters['type']))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'marketplace', 'image_path']);

        $products = Product::query()
            ->where('is_active', true)
            ->whereHas('business', fn ($query) => $query->where('status', ApprovalStatus::Approved))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with(['business:id,name,slug', 'category:id,name,slug,marketplace', 'libraryImage'])
            ->when(isset($filters['type']), fn ($query) => $query->where('product_type', $filters['type']))
            ->when(isset($filters['category_id']), fn ($query) => $query->where('category_id', $filters['category_id']))
            ->when(isset($filters['q']), function ($query) use ($filters): void {
                $term = '%'.$filters['q'].'%';
                $query->where(fn ($search) => $search
                    ->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->latest()
            ->paginate(24);

        return response()->json([
            'data' => $products->items(),
            'categories' => $categories,
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
