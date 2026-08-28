<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackSevenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Jewellery & Accessories', 'Gold Necklace', 'gold-necklace', ['gold necklace', 'necklace', 'jewellery', 'सोने का हार', 'नेकलेस'], 'jewellery-accessories/gold-necklace.webp'],
            ['Jewellery & Accessories', 'Earrings', 'earrings', ['earrings', 'gold earrings', 'jewellery', 'कान की बाली', 'ईयररिंग'], 'jewellery-accessories/earrings.webp'],
            ['Jewellery & Accessories', 'Bangles', 'bangles', ['bangles', 'gold bangles', 'bracelets', 'चूड़ी', 'कंगन'], 'jewellery-accessories/bangles.webp'],
            ['Jewellery & Accessories', 'Finger Ring', 'finger-ring', ['finger ring', 'gold ring', 'gemstone ring', 'अंगूठी', 'रिंग'], 'jewellery-accessories/finger-ring.webp'],
            ['Jewellery & Accessories', 'Wristwatch', 'wristwatch', ['wristwatch', 'analog watch', 'watch', 'कलाई घड़ी', 'वॉच'], 'jewellery-accessories/wristwatch.webp'],
            ['Jewellery & Accessories', 'Sunglasses', 'sunglasses', ['sunglasses', 'eyewear', 'dark glasses', 'धूप का चश्मा', 'सनग्लास'], 'jewellery-accessories/sunglasses.webp'],
            ['Automotive', 'Motorcycle Helmet', 'motorcycle-helmet', ['motorcycle helmet', 'bike helmet', 'full face helmet', 'मोटरसाइकिल हेलमेट', 'बाइक हेलमेट'], 'automotive/motorcycle-helmet.webp'],
            ['Automotive', 'Car Cleaning Kit', 'car-cleaning-kit', ['car cleaning kit', 'microfiber cloth', 'car wash', 'कार क्लीनिंग किट', 'सफाई'], 'automotive/car-cleaning-kit.webp'],
            ['Automotive', 'Engine Oil Bottle', 'engine-oil-bottle', ['engine oil', 'motor oil', 'lubricant', 'इंजन ऑयल', 'मोटर तेल'], 'automotive/engine-oil-bottle.webp'],
            ['Automotive', 'Car Seat Cover', 'car-seat-cover', ['car seat cover', 'seat cover', 'car interior', 'कार सीट कवर', 'सीट कवर'], 'automotive/car-seat-cover.webp'],
            ['Automotive', 'Portable Tyre Inflator', 'portable-tyre-inflator', ['tyre inflator', 'air compressor', 'tyre pump', 'टायर इन्फ्लेटर', 'हवा पंप'], 'automotive/portable-tyre-inflator.webp'],
            ['Automotive', 'Car Mobile Holder', 'car-mobile-holder', ['car mobile holder', 'phone mount', 'dashboard holder', 'कार मोबाइल होल्डर', 'फोन स्टैंड'], 'automotive/car-mobile-holder.webp'],
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
