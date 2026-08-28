<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackFourSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Fashion', "Men's Shirt", 'mens-shirt', ['mens shirt', 'shirt', 'kamiz', 'fashion'], 'fashion/mens-shirt.webp'],
            ['Fashion', 'Saree', 'saree', ['saree', 'sari', 'saadi', 'women fashion'], 'fashion/saree.webp'],
            ['Fashion', "Women's Kurti", 'womens-kurti', ['kurti', 'women kurti', 'ladies wear', 'fashion'], 'fashion/womens-kurti.webp'],
            ['Fashion', 'Jeans', 'jeans', ['jeans', 'denim', 'pant', 'fashion'], 'fashion/jeans.webp'],
            ['Fashion', 'Casual Shoes', 'casual-shoes', ['shoes', 'casual shoes', 'sneakers', 'footwear'], 'fashion/casual-shoes.webp'],
            ['Fashion', 'Handbag', 'handbag', ['handbag', 'purse', 'ladies bag', 'fashion accessory'], 'fashion/handbag.webp'],
            ['Education & Stationery', 'Notebooks', 'notebooks', ['notebook', 'copy', 'exercise book', 'stationery'], 'education-stationery/notebooks.webp'],
            ['Education & Stationery', 'School Backpack', 'school-backpack', ['school bag', 'backpack', 'student bag', 'education'], 'education-stationery/school-backpack.webp'],
            ['Education & Stationery', 'Geometry Box', 'geometry-box', ['geometry box', 'compass box', 'math set', 'stationery'], 'education-stationery/geometry-box.webp'],
            ['Education & Stationery', 'Writing Set', 'writing-set', ['pen', 'pencil', 'eraser', 'sharpener', 'stationery'], 'education-stationery/writing-set.webp'],
            ['Education & Stationery', 'Calculator', 'calculator', ['calculator', 'student calculator', 'office stationery'], 'education-stationery/calculator.webp'],
            ['Education & Stationery', 'Art Supplies', 'art-supplies', ['art set', 'colours', 'crayons', 'paint', 'school supplies'], 'education-stationery/art-supplies.webp'],
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
