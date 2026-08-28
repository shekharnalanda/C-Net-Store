<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackTwoSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Fruits & Vegetables', 'Apples', 'apples', ['apple', 'apples', 'seb', 'fruit'], 'fruits-vegetables/apples.webp'],
            ['Fruits & Vegetables', 'Bananas', 'bananas', ['banana', 'bananas', 'kela', 'fruit'], 'fruits-vegetables/bananas.webp'],
            ['Fruits & Vegetables', 'Mangoes', 'mangoes', ['mango', 'mangoes', 'aam', 'fruit'], 'fruits-vegetables/mangoes.webp'],
            ['Fruits & Vegetables', 'Tomatoes', 'tomatoes', ['tomato', 'tomatoes', 'tamatar', 'vegetable'], 'fruits-vegetables/tomatoes.webp'],
            ['Fruits & Vegetables', 'Potatoes', 'potatoes', ['potato', 'potatoes', 'aloo', 'vegetable'], 'fruits-vegetables/potatoes.webp'],
            ['Fruits & Vegetables', 'Onions', 'onions', ['onion', 'onions', 'pyaz', 'vegetable'], 'fruits-vegetables/onions.webp'],
            ['Beauty & Personal Care', 'Lipstick', 'lipstick', ['lipstick', 'lip colour', 'makeup', 'beauty'], 'beauty-personal-care/lipstick.webp'],
            ['Beauty & Personal Care', 'Face Cream', 'face-cream', ['face cream', 'skin care', 'moisturizer', 'beauty'], 'beauty-personal-care/face-cream.webp'],
            ['Beauty & Personal Care', 'Shampoo', 'shampoo', ['shampoo', 'hair wash', 'hair care', 'personal care'], 'beauty-personal-care/shampoo.webp'],
            ['Beauty & Personal Care', 'Hair Oil', 'hair-oil', ['hair oil', 'tel', 'hair care', 'personal care'], 'beauty-personal-care/hair-oil.webp'],
            ['Beauty & Personal Care', 'Bath Soap', 'bath-soap', ['soap', 'bath soap', 'sabun', 'personal care'], 'beauty-personal-care/bath-soap.webp'],
            ['Beauty & Personal Care', 'Makeup Kit', 'makeup-kit', ['makeup kit', 'cosmetics', 'brush', 'beauty'], 'beauty-personal-care/makeup-kit.webp'],
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
