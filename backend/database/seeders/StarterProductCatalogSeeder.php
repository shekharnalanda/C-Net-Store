<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StarterProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $businessId = DB::table('businesses')
            ->where('status', 'approved')
            ->orderBy('id')
            ->value('id');

        if (! $businessId) {
            throw new RuntimeException('An approved business is required before creating catalog drafts.');
        }

        $outletId = DB::table('outlets')
            ->where('business_id', $businessId)
            ->where('status', 'approved')
            ->orderBy('id')
            ->value('id');

        if (! $outletId) {
            throw new RuntimeException('An approved outlet is required before creating catalog drafts.');
        }

        $assets = DB::table('product_image_assets as assets')
            ->join('categories', 'categories.id', '=', 'assets.category_id')
            ->where('assets.is_active', true)
            ->where('categories.is_active', true)
            ->select([
                'assets.id',
                'assets.category_id',
                'assets.name',
                'assets.slug',
                'categories.marketplace',
            ])
            ->orderBy('assets.id')
            ->get();

        $created = 0;

        DB::transaction(function () use ($assets, $businessId, $outletId, &$created): void {
            foreach ($assets as $asset) {
                $sku = 'CNET-CATALOG-'.str_pad((string) $asset->id, 4, '0', STR_PAD_LEFT);
                $productId = DB::table('products')
                    ->where('business_id', $businessId)
                    ->where('sku', $sku)
                    ->value('id');

                if (! $productId) {
                    $productId = DB::table('products')->insertGetId([
                        'business_id' => $businessId,
                        'category_id' => $asset->category_id,
                        'product_image_asset_id' => $asset->id,
                        'name' => $asset->name,
                        'slug' => 'catalog-'.$asset->slug,
                        'sku' => $sku,
                        'description' => 'Catalog draft generated from the approved C-Net Store image library. Verify price and stock before activation.',
                        'image_path' => null,
                        'product_type' => $asset->marketplace,
                        'price' => 0,
                        'sale_price' => null,
                        'tax_rate' => 0,
                        'stock_quantity' => 0,
                        'unit' => $asset->marketplace === 'food' ? 'plate' : 'piece',
                        'preparation_minutes' => $asset->marketplace === 'food' ? 30 : null,
                        'is_vegetarian' => null,
                        'is_active' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $created++;
                }

                DB::table('inventory')->insertOrIgnore([
                    'outlet_id' => $outletId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'low_stock_threshold' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $total = DB::table('products')
            ->where('business_id', $businessId)
            ->where('sku', 'like', 'CNET-CATALOG-%')
            ->count();

        $this->command?->info("STARTER_PRODUCT_DRAFTS_CREATED={$created}");
        $this->command?->info("STARTER_PRODUCT_DRAFTS_TOTAL={$total}");
    }
}
