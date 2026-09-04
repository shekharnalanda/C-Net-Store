<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'draft'])],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $products = Product::query()
            ->with(['business', 'category', 'libraryImage'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'draft', fn ($query) => $query->where('is_active', false))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $counts = [
            'all' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'draft' => Product::where('is_active', false)->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'counts'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'sale_price' => ['nullable', 'numeric', 'min:0.01', 'lte:price', 'max:9999999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:999999999'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($data['is_active'] && ((float) $data['price'] <= 0 || (int) $data['stock_quantity'] <= 0)) {
            return back()
                ->withErrors(['is_active' => 'Activation requires a price above ₹0 and stock above 0.'])
                ->withInput();
        }

        DB::transaction(function () use ($product, $data): void {
            $product->update($data);

            $outletId = DB::table('outlets')
                ->where('business_id', $product->business_id)
                ->where('status', 'approved')
                ->value('id') ?? DB::table('outlets')
                    ->where('business_id', $product->business_id)
                    ->value('id');

            if ($outletId) {
                DB::table('inventory')->updateOrInsert(
                    ['outlet_id' => $outletId, 'product_id' => $product->id],
                    [
                        'quantity' => $data['stock_quantity'],
                        'reserved_quantity' => 0,
                        'low_stock_threshold' => 5,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        return back()->with('success', $product->name.' updated successfully.');
    }

    public function bulkActivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:200'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        $selectedIds = $data['product_ids'];
        $eligibleIds = Product::query()
            ->whereIn('id', $selectedIds)
            ->where('price', '>', 0)
            ->where('stock_quantity', '>', 0)
            ->whereHas('inventory', fn ($query) => $query->where('quantity', '>', 0))
            ->pluck('id');

        Product::whereIn('id', $eligibleIds)->update(['is_active' => true]);

        $activated = $eligibleIds->count();
        $skipped = count($selectedIds) - $activated;

        return back()->with(
            'success',
            $activated.' product(s) activated. '.$skipped.' skipped because price or stock is missing.'
        );
    }
}
