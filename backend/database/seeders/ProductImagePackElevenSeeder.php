<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackElevenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Bags & Luggage', 'Backpack', 'bag-backpack', ['backpack', 'school bag', 'travel backpack', 'बैगपैक', 'स्कूल बैग'], 'bags-luggage/backpack.webp'],
            ['Bags & Luggage', 'Trolley Suitcase', 'bag-trolley-suitcase', ['trolley suitcase', 'luggage bag', 'travel suitcase', 'ट्रॉली बैग', 'सूटकेस'], 'bags-luggage/trolley-suitcase.webp'],
            ['Bags & Luggage', 'Handbag', 'bag-handbag', ['handbag', 'ladies bag', 'purse', 'हैंडबैग', 'महिला बैग'], 'bags-luggage/handbag.webp'],
            ['Bags & Luggage', 'Duffel Bag', 'bag-duffel', ['duffel bag', 'gym bag', 'travel bag', 'डफल बैग', 'ट्रैवल बैग'], 'bags-luggage/duffel-bag.webp'],
            ['Bags & Luggage', 'Laptop Bag', 'bag-laptop', ['laptop bag', 'computer bag', 'office bag', 'लैपटॉप बैग', 'ऑफिस बैग'], 'bags-luggage/laptop-bag.webp'],
            ['Bags & Luggage', 'Travel Pouch', 'bag-travel-pouch', ['travel pouch', 'organizer pouch', 'toiletry bag', 'ट्रैवल पाउच', 'ऑर्गनाइजर बैग'], 'bags-luggage/travel-pouch.webp'],
            ['Footwear', 'Running Shoes', 'footwear-running-shoes', ['running shoes', 'sports shoes', 'sneakers', 'रनिंग शूज', 'स्पोर्ट्स जूते'], 'footwear/running-shoes.webp'],
            ['Footwear', 'Formal Shoes', 'footwear-formal-shoes', ['formal shoes', 'office shoes', 'black shoes', 'फॉर्मल जूते', 'ऑफिस शूज'], 'footwear/formal-shoes.webp'],
            ['Footwear', 'Sandals', 'footwear-sandals', ['sandals', 'strap sandals', 'daily sandals', 'सैंडल', 'पट्टा चप्पल'], 'footwear/sandals.webp'],
            ['Footwear', 'Slippers', 'footwear-slippers', ['slippers', 'house slippers', 'slides', 'चप्पल', 'स्लिपर'], 'footwear/slippers.webp'],
            ['Footwear', 'Kids Shoes', 'footwear-kids-shoes', ['kids shoes', 'children shoes', 'velcro shoes', 'बच्चों के जूते', 'किड्स शूज'], 'footwear/kids-shoes.webp'],
            ['Footwear', 'Women Flats', 'footwear-women-flats', ['women flats', 'ladies shoes', 'ballet flats', 'महिला फ्लैट्स', 'लेडीज जूते'], 'footwear/women-flats.webp'],
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
