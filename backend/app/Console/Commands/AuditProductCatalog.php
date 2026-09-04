<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AuditProductCatalog extends Command
{
    protected $signature = 'store:catalog-audit';

    protected $description = 'Audit C-Net Store product publication readiness and inventory safety';

    public function handle(): int
    {
        $matchingInventory = fn ($query) => $query
            ->where('quantity', '>', 0)
            ->whereColumn('inventory.quantity', 'products.stock_quantity');

        $readyDrafts = Product::query()
            ->where('is_active', false)
            ->where('price', '>', 0)
            ->where('stock_quantity', '>', 0)
            ->whereHas('inventory', $matchingInventory)
            ->count();

        $unsafeActive = Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($matchingInventory): void {
                $query->where('price', '<=', 0)
                    ->orWhere('stock_quantity', '<=', 0)
                    ->orWhereDoesntHave('inventory', $matchingInventory);
            })
            ->count();

        $inventoryMismatches = Product::query()
            ->where('stock_quantity', '>', 0)
            ->whereDoesntHave('inventory', $matchingInventory)
            ->count();

        $rows = [
            ['All products', Product::count()],
            ['Active products', Product::where('is_active', true)->count()],
            ['Draft products', Product::where('is_active', false)->count()],
            ['Ready drafts', $readyDrafts],
            ['Drafts missing price', Product::where('is_active', false)->where('price', '<=', 0)->count()],
            ['Drafts missing stock', Product::where('is_active', false)->where('stock_quantity', '<=', 0)->count()],
            ['Inventory mismatches', $inventoryMismatches],
            ['Unsafe active products', $unsafeActive],
        ];

        $this->table(['Metric', 'Result'], $rows);

        if ($unsafeActive > 0) {
            $this->error('CNET_CATALOG_AUDIT=FAIL');

            return self::FAILURE;
        }

        $this->info('CNET_CATALOG_AUDIT=PASS');

        return self::SUCCESS;
    }
}
