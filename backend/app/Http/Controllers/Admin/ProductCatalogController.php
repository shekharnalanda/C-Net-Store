<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
            'ready' => $this->readyDraftQuery()->count(),
            'missing_price' => Product::where('is_active', false)->where('price', '<=', 0)->count(),
            'missing_stock' => Product::where('is_active', false)->where('stock_quantity', '<=', 0)->count(),
            'inventory_mismatch' => Product::where('stock_quantity', '>', 0)
                ->whereDoesntHave('inventory', fn ($query) => $query->whereColumn('inventory.quantity', 'products.stock_quantity'))
                ->count(),
        ];

        return view('admin.products.index', compact('products', 'categories', 'counts'));
    }


    public function export(): StreamedResponse
    {
        $filename = 'cnet-product-catalog-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['sku', 'name', 'category', 'price', 'sale_price', 'stock_quantity', 'unit', 'tax_rate', 'is_active']);

            Product::query()
                ->with('category:id,name')
                ->orderBy('id')
                ->chunk(200, function ($products) use ($handle): void {
                    foreach ($products as $product) {
                        fputcsv($handle, [
                            $this->safeCsvCell($product->sku),
                            $this->safeCsvCell($product->name),
                            $this->safeCsvCell($product->category?->name),
                            $product->price,
                            $product->sale_price,
                            $product->stock_quantity,
                            $this->safeCsvCell($product->unit),
                            $product->tax_rate,
                            $product->is_active ? 1 : 0,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog_csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('catalog_csv');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            throw ValidationException::withMessages(['catalog_csv' => 'The CSV file could not be opened.']);
        }

        $expected = ['sku', 'name', 'category', 'price', 'sale_price', 'stock_quantity', 'unit', 'tax_rate', 'is_active'];
        $header = array_map(fn ($value) => strtolower(trim((string) $value)), fgetcsv($handle) ?: []);

        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages([
                'catalog_csv' => 'CSV columns do not match the exported C-Net catalog template.',
            ]);
        }

        $updated = 0;
        $skipped = 0;
        $line = 1;

        DB::transaction(function () use ($handle, &$updated, &$skipped, &$line): void {
            while (($row = fgetcsv($handle)) !== false) {
                $line++;

                if ($line > 1001) {
                    $skipped++;
                    continue;
                }

                $row = array_pad($row, 9, null);
                [$sku, , , $price, $salePrice, $stock, $unit, $taxRate, $active] = $row;
                $product = Product::where('sku', trim((string) $sku))->first();

                $valid = $product
                    && is_numeric($price) && (float) $price >= 0 && (float) $price <= 9999999999.99
                    && ($salePrice === '' || $salePrice === null || (is_numeric($salePrice) && (float) $salePrice > 0 && (float) $salePrice <= (float) $price))
                    && filter_var($stock, FILTER_VALIDATE_INT) !== false && (int) $stock >= 0
                    && is_numeric($taxRate) && (float) $taxRate >= 0 && (float) $taxRate <= 100
                    && mb_strlen(trim((string) $unit)) <= 30
                    && in_array(trim((string) $active), ['0', '1'], true);

                if (! $valid) {
                    $skipped++;
                    continue;
                }

                $shouldActivate = trim((string) $active) === '1'
                    && (float) $price > 0
                    && (int) $stock > 0;

                $product->update([
                    'price' => $price,
                    'sale_price' => ($salePrice === '' || $salePrice === null) ? null : $salePrice,
                    'stock_quantity' => $stock,
                    'unit' => trim((string) $unit) ?: null,
                    'tax_rate' => $taxRate,
                    'is_active' => $shouldActivate,
                ]);

                $this->syncInventory($product, (int) $stock);
                $updated++;
            }
        });

        fclose($handle);

        return back()
            ->with('success', $updated.' product(s) updated from CSV.')
            ->with('warning', $skipped ? $skipped.' invalid or extra row(s) were skipped safely.' : null);
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

            $this->syncInventory($product, (int) $data['stock_quantity']);
        });

        return back()->with('success', $product->name.' updated successfully.');
    }


    public function activateReady(): RedirectResponse
    {
        $readyIds = $this->readyDraftQuery()->pluck('id');
        Product::whereIn('id', $readyIds)->update(['is_active' => true]);

        return back()->with(
            'success',
            $readyIds->count().' fully prepared draft product(s) activated safely.'
        );
    }

    public function bulkDeactivate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:200'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ]);

        $deactivated = Product::whereIn('id', $data['product_ids'])
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return back()->with('success', $deactivated.' selected product(s) moved to draft.');
    }

    private function readyDraftQuery(): Builder
    {
        return Product::query()
            ->where('is_active', false)
            ->where('price', '>', 0)
            ->where('stock_quantity', '>', 0)
            ->whereHas('inventory', function ($query): void {
                $query->where('quantity', '>', 0)
                    ->whereColumn('inventory.quantity', 'products.stock_quantity');
            });
    }

    private function syncInventory(Product $product, int $quantity): void
    {
        $outletId = DB::table('outlets')
            ->where('business_id', $product->business_id)
            ->where('status', 'approved')
            ->value('id') ?? DB::table('outlets')
                ->where('business_id', $product->business_id)
                ->value('id');

        if (! $outletId) {
            return;
        }

        $exists = DB::table('inventory')
            ->where('outlet_id', $outletId)
            ->where('product_id', $product->id)
            ->exists();

        DB::table('inventory')->updateOrInsert(
            ['outlet_id' => $outletId, 'product_id' => $product->id],
            [
                'quantity' => $quantity,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
                'updated_at' => now(),
                ...($exists ? [] : ['created_at' => now()]),
            ]
        );
    }

    private function safeCsvCell(?string $value): ?string
    {
        if ($value !== null && preg_match('/^[=+\-@]/', $value) === 1) {
            return "'".$value;
        }

        return $value;
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
