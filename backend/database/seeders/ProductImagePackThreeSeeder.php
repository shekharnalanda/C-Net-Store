<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackThreeSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Electronics', 'Smartphone', 'smartphone', ['smartphone', 'mobile', 'phone', 'electronics'], 'electronics/smartphone.webp'],
            ['Electronics', 'Laptop', 'laptop', ['laptop', 'notebook', 'computer', 'electronics'], 'electronics/laptop.webp'],
            ['Electronics', 'Wireless Headphones', 'wireless-headphones', ['headphones', 'wireless', 'audio', 'electronics'], 'electronics/wireless-headphones.webp'],
            ['Electronics', 'LED Television', 'led-television', ['television', 'tv', 'led tv', 'electronics'], 'electronics/led-television.webp'],
            ['Electronics', 'Bluetooth Speaker', 'bluetooth-speaker', ['speaker', 'bluetooth', 'portable audio', 'electronics'], 'electronics/bluetooth-speaker.webp'],
            ['Electronics', 'Smartwatch', 'smartwatch', ['smartwatch', 'watch', 'wearable', 'electronics'], 'electronics/smartwatch.webp'],
            ['Home & Kitchen', 'Pressure Cooker', 'pressure-cooker', ['pressure cooker', 'cooker', 'kitchen', 'bartan'], 'home-kitchen/pressure-cooker.webp'],
            ['Home & Kitchen', 'Frying Pan', 'frying-pan', ['frying pan', 'non stick pan', 'kadhai', 'kitchen'], 'home-kitchen/frying-pan.webp'],
            ['Home & Kitchen', 'Dinner Set', 'dinner-set', ['dinner set', 'plates', 'bowls', 'crockery'], 'home-kitchen/dinner-set.webp'],
            ['Home & Kitchen', 'Bedsheet', 'bedsheet', ['bedsheet', 'bed sheet', 'bedding', 'home furnishing'], 'home-kitchen/bedsheet.webp'],
            ['Home & Kitchen', 'Storage Containers', 'storage-containers', ['storage container', 'kitchen container', 'dabba', 'organizer'], 'home-kitchen/storage-containers.webp'],
            ['Home & Kitchen', 'Electric Kettle', 'electric-kettle', ['electric kettle', 'kettle', 'water heater', 'kitchen appliance'], 'home-kitchen/electric-kettle.webp'],
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
