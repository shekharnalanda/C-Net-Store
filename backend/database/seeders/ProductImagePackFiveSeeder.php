<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackFiveSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Toys & Baby', 'Building Blocks', 'building-blocks', ['building blocks', 'blocks', 'kids toy', 'बिल्डिंग ब्लॉक्स', 'खिलौना'], 'toys-baby/building-blocks.webp'],
            ['Toys & Baby', 'Toy Car', 'toy-car', ['toy car', 'kids car', 'vehicle toy', 'टॉय कार', 'खिलौना गाड़ी'], 'toys-baby/toy-car.webp'],
            ['Toys & Baby', 'Doll', 'doll', ['doll', 'kids doll', 'girl toy', 'गुड़िया', 'खिलौना'], 'toys-baby/doll.webp'],
            ['Toys & Baby', 'Teddy Bear', 'teddy-bear', ['teddy bear', 'soft toy', 'plush toy', 'टेडी बियर', 'सॉफ्ट टॉय'], 'toys-baby/teddy-bear.webp'],
            ['Toys & Baby', 'Baby Bottle', 'baby-bottle', ['baby bottle', 'feeding bottle', 'infant care', 'बेबी बोतल', 'फीडिंग बोतल'], 'toys-baby/baby-bottle.webp'],
            ['Toys & Baby', 'Baby Stroller', 'baby-stroller', ['baby stroller', 'pram', 'baby carriage', 'बेबी स्ट्रोलर', 'बच्चे की गाड़ी'], 'toys-baby/baby-stroller.webp'],
            ['Sports & Fitness', 'Cricket Set', 'cricket-set', ['cricket set', 'bat', 'ball', 'stumps', 'क्रिकेट सेट', 'बल्ला'], 'sports-fitness/cricket-set.webp'],
            ['Sports & Fitness', 'Football', 'football', ['football', 'soccer ball', 'sports ball', 'फुटबॉल', 'खेल'], 'sports-fitness/football.webp'],
            ['Sports & Fitness', 'Badminton Set', 'badminton-set', ['badminton set', 'racket', 'shuttlecock', 'बैडमिंटन', 'रैकेट'], 'sports-fitness/badminton-set.webp'],
            ['Sports & Fitness', 'Yoga Mat', 'yoga-mat', ['yoga mat', 'exercise mat', 'fitness mat', 'योगा मैट', 'व्यायाम'], 'sports-fitness/yoga-mat.webp'],
            ['Sports & Fitness', 'Dumbbells', 'dumbbells', ['dumbbells', 'weights', 'gym equipment', 'डम्बल', 'जिम'], 'sports-fitness/dumbbells.webp'],
            ['Sports & Fitness', 'Skipping Rope', 'skipping-rope', ['skipping rope', 'jump rope', 'cardio', 'स्किपिंग रोप', 'रस्सी कूद'], 'sports-fitness/skipping-rope.webp'],
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
