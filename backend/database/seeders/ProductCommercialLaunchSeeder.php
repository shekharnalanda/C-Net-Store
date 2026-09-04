<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductCommercialLaunchSeeder extends Seeder
{
    public function run(): void
    {
        $priceMap = [
            'Agriculture' => [299, 799, 1499, 349, 999, 699],
            'Automotive' => [1199, 499, 549, 899, 2499, 699],
            'Bags & Luggage' => [799, 2499, 699, 1199, 999, 249],
            'Beauty & Personal Care' => [299, 349, 299, 249, 99, 999],
            'Bihar Foods' => [249, 199, 149, 449, 299, 399],
            'Bihar Handicrafts' => [999, 399, 699, 1499, 349, 1299],
            'Books' => [499, 249, 599, 299, 399, 349],
            'Cleaning Products' => [149, 119, 179, 99, 299, 129],
            'Construction Materials' => [449, 12, 3999, 699, 349, 59],
            'Education & Stationery' => [99, 699, 149, 199, 499, 299],
            'Electronics' => [14999, 44999, 1999, 24999, 2499, 2999],
            'Fashion' => [799, 1199, 999, 1299, 999, 699],
            'Food' => [199, 169, 199, 229, 249, 129],
            'Footwear' => [1199, 1499, 599, 399, 799, 699],
            'Fruits & Vegetables' => [199, 69, 149, 49, 39, 49],
            'Garden & Outdoor' => [499, 299, 799, 1299, 2499, 499],
            'Gifts & Handicrafts' => [699, 999, 899, 599, 349, 799],
            'Grocery' => [699, 299, 179, 149, 249, 59],
            'Hardware & Tools' => [399, 599, 499, 3499, 249, 299],
            'Health & Wellness' => [599, 299, 1499, 699, 299, 149],
            'Home & Kitchen' => [2499, 899, 1999, 799, 699, 1499],
            'Home Appliances' => [3499, 1499, 1799, 1199, 3499, 2999],
            'Jewellery & Accessories' => [1499, 499, 699, 399, 999, 699],
            'Medical Supplies' => [599, 299, 1499, 149, 199, 99],
            'Mobile Accessories' => [499, 1299, 999, 299, 199, 249],
            'Office Supplies' => [149, 249, 399, 299, 5999, 699],
            'Pet Supplies' => [699, 399, 999, 499, 599, 299],
            'Puja Essentials' => [299, 199, 999, 499, 299, 249],
            'Sports & Fitness' => [1999, 699, 1499, 599, 999, 299],
            'Toys & Baby' => [499, 699, 599, 499, 299, 4999],
        ];

        $stockMap = [
            'Construction Materials' => [40, 1000, 50, 120, 100, 500],
            'Electronics' => [15, 8, 25, 10, 20, 20],
            'Food' => [40, 40, 40, 30, 30, 40],
            'Fruits & Vegetables' => [80, 100, 60, 120, 120, 120],
            'Grocery' => [60, 80, 80, 70, 60, 100],
            'Home Appliances' => [10, 15, 15, 20, 10, 10],
        ];

        $assets = DB::table('product_image_assets')
            ->where('is_active', true)
            ->orderBy('group_name')
            ->orderBy('id')
            ->get()
            ->groupBy('group_name');

        foreach ($priceMap as $group => $prices) {
            if (! isset($assets[$group]) || $assets[$group]->count() !== count($prices)) {
                throw new RuntimeException("Expected six active assets for {$group}.");
            }
        }

        $updated = 0;

        DB::transaction(function () use ($assets, $priceMap, $stockMap, &$updated): void {
            foreach ($priceMap as $group => $prices) {
                foreach ($assets[$group]->values() as $index => $asset) {
                    $product = DB::table('products')
                        ->where('product_image_asset_id', $asset->id)
                        ->where('sku', 'like', 'CNET-CATALOG-%')
                        ->first();

                    if (! $product) {
                        throw new RuntimeException("Missing starter product for image asset {$asset->id}.");
                    }

                    $price = $prices[$index];
                    $stock = $stockMap[$group][$index] ?? 25;
                    $foodGroup = in_array($group, ['Food', 'Bihar Foods'], true);
                    $taxRate = match ($group) {
                        'Food', 'Bihar Foods', 'Fruits & Vegetables', 'Grocery' => 5,
                        'Electronics', 'Home Appliances', 'Mobile Accessories' => 18,
                        default => 12,
                    };

                    DB::table('products')->where('id', $product->id)->update([
                        'description' => $asset->name.' available from C-Net Store. Final brand, size and specifications are shown at order confirmation.',
                        'price' => $price,
                        'sale_price' => null,
                        'tax_rate' => $taxRate,
                        'stock_quantity' => $stock,
                        'unit' => $foodGroup ? 'plate' : 'piece',
                        'preparation_minutes' => $foodGroup ? 30 : null,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

                    DB::table('inventory')->where('product_id', $product->id)->update([
                        'quantity' => $stock,
                        'low_stock_threshold' => min(5, max(1, (int) floor($stock / 5))),
                        'updated_at' => now(),
                    ]);

                    $updated++;
                }
            }
        });

        $this->command?->info("COMMERCIAL_PRODUCTS_PUBLISHED={$updated}");
    }
}
