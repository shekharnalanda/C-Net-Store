<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImageStarterSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Food', 'Chicken Biryani', 'chicken-biryani', ['biryani', 'chicken', 'rice', 'non veg'], 'food/chicken-biryani.webp'],
            ['Food', 'Vegetable Biryani', 'vegetable-biryani', ['biryani', 'vegetable', 'rice', 'veg'], 'food/veg-biryani.webp'],
            ['Food', 'Vegetarian Thali', 'vegetarian-thali', ['thali', 'rice', 'dal', 'roti', 'veg meal'], 'food/veg-thali.webp'],
            ['Food', 'Paneer Butter Masala', 'paneer-butter-masala', ['paneer', 'curry', 'vegetarian'], 'food/paneer-butter-masala.webp'],
            ['Food', 'Vegetable Pizza', 'vegetable-pizza', ['pizza', 'cheese', 'vegetarian', 'fast food'], 'food/vegetable-pizza.webp'],
            ['Food', 'Vegetable Burger', 'vegetable-burger', ['burger', 'vegetarian', 'fast food'], 'food/veg-burger.webp'],
            ['Grocery', 'Basmati Rice', 'basmati-rice', ['rice', 'chawal', 'grain'], 'grocery/basmati-rice.webp'],
            ['Grocery', 'Wheat Flour', 'wheat-flour', ['flour', 'atta', 'wheat'], 'grocery/wheat-flour.webp'],
            ['Grocery', 'Toor Dal', 'toor-dal', ['dal', 'lentil', 'pulse', 'arhar'], 'grocery/toor-dal.webp'],
            ['Grocery', 'Cooking Oil', 'cooking-oil', ['oil', 'edible oil', 'grocery'], 'grocery/cooking-oil.webp'],
            ['Grocery', 'Indian Spices', 'indian-spices', ['masala', 'turmeric', 'chilli', 'cumin', 'spices'], 'grocery/indian-spices.webp'],
            ['Grocery', 'Sugar', 'sugar', ['sugar', 'chini', 'grocery'], 'grocery/sugar.webp'],
        ];

        foreach ($assets as $sort => [$group, $name, $slug, $keywords, $path]) {
            ProductImageAsset::updateOrCreate(
                ['slug' => 'cnet-original-'.$slug],
                [
                    'category_id' => null,
                    'group_name' => $group,
                    'name' => $name,
                    'keywords' => $keywords,
                    'image_path' => 'product-image-library/'.$path,
                    'alt_text' => $name.' product image',
                    'license_type' => 'cnet_original',
                    'license_source' => null,
                    'is_active' => true,
                    'sort_order' => $sort + 1,
                ]
            );
        }
    }
}
