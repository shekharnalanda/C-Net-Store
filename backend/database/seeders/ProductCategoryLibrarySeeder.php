<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategoryLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $marketplaces = [
            'Food' => 'food',
            'Bihar Foods' => 'food',

            'Grocery' => 'grocery',
            'Fruits & Vegetables' => 'grocery',
            'Cleaning Products' => 'grocery',
            'Puja Essentials' => 'grocery',

            'Agriculture' => 'shopping',
            'Automotive' => 'shopping',
            'Bags & Luggage' => 'shopping',
            'Beauty & Personal Care' => 'shopping',
            'Bihar Handicrafts' => 'shopping',
            'Books' => 'shopping',
            'Construction Materials' => 'shopping',
            'Education & Stationery' => 'shopping',
            'Electronics' => 'shopping',
            'Fashion' => 'shopping',
            'Footwear' => 'shopping',
            'Garden & Outdoor' => 'shopping',
            'Gifts & Handicrafts' => 'shopping',
            'Hardware & Tools' => 'shopping',
            'Health & Wellness' => 'shopping',
            'Home & Kitchen' => 'shopping',
            'Home Appliances' => 'shopping',
            'Jewellery & Accessories' => 'shopping',
            'Medical Supplies' => 'shopping',
            'Mobile Accessories' => 'shopping',
            'Office Supplies' => 'shopping',
            'Pet Supplies' => 'shopping',
            'Sports & Fitness' => 'shopping',
            'Toys & Baby' => 'shopping',
        ];

        DB::transaction(function () use ($marketplaces): void {
            $groups = DB::table('product_image_assets')
                ->select('group_name')
                ->selectRaw('MIN(image_path) AS image_path')
                ->groupBy('group_name')
                ->orderBy('group_name')
                ->get();

            foreach ($groups as $index => $group) {
                $slug = Str::slug($group->group_name);
                $marketplace = $marketplaces[$group->group_name]
                    ?? 'shopping';

                DB::table('categories')->updateOrInsert(
                    ['slug' => $slug],
                    [
                        'parent_id' => null,
                        'name' => $group->group_name,
                        'marketplace' => $marketplace,
                        'image_path' => $group->image_path,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => DB::raw(
                            'COALESCE(created_at, CURRENT_TIMESTAMP)'
                        ),
                    ]
                );

                $categoryId = DB::table('categories')
                    ->where('slug', $slug)
                    ->value('id');

                DB::table('product_image_assets')
                    ->where('group_name', $group->group_name)
                    ->update([
                        'category_id' => $categoryId,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('products')
                ->where('sku', 'like', 'CNET-LIVE-TEST-%')
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            DB::table('categories')
                ->where('slug', 'live-payment-verification')
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        });

        $this->command?->info(
            'PRODUCT_CATEGORY_LIBRARY_SEEDED=YES'
        );
    }
}
