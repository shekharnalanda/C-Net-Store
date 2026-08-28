<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackFourteenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Puja Essentials', 'Brass Diya', 'puja-brass-diya', ['brass diya', 'oil lamp', 'पीतल का दीया', 'पूजा दीपक', 'आरती दीया'], 'puja-essentials/brass-diya.webp'],
            ['Puja Essentials', 'Incense Sticks and Holder', 'puja-incense-sticks-holder', ['incense sticks', 'agarbatti holder', 'अगरबत्ती', 'अगरबत्ती स्टैंड', 'धूपबत्ती'], 'puja-essentials/incense-sticks-holder.webp'],
            ['Puja Essentials', 'Brass Puja Thali Set', 'puja-brass-thali-set', ['puja thali set', 'brass worship plate', 'पूजा थाली', 'पीतल पूजा सेट', 'आरती थाली'], 'puja-essentials/brass-puja-thali.webp'],
            ['Puja Essentials', 'Brass Kalash', 'puja-brass-kalash', ['brass kalash', 'puja vessel', 'पीतल कलश', 'पूजा कलश', 'मंगल कलश'], 'puja-essentials/brass-kalash.webp'],
            ['Puja Essentials', 'Brass Prayer Bell', 'puja-brass-prayer-bell', ['brass prayer bell', 'puja bell', 'पूजा घंटी', 'पीतल घंटी', 'आरती घंटी'], 'puja-essentials/brass-prayer-bell.webp'],
            ['Puja Essentials', 'Cotton Diya Wicks', 'puja-cotton-diya-wicks', ['cotton diya wicks', 'puja cotton wick', 'रुई की बत्ती', 'दीया बाती', 'पूजा बत्ती'], 'puja-essentials/cotton-diya-wicks.webp'],
            ['Gifts & Handicrafts', 'Decorative Gift Box', 'gift-decorative-box', ['decorative gift box', 'wrapped present', 'गिफ्ट बॉक्स', 'उपहार डिब्बा', 'सजावटी पैकिंग'], 'gifts-handicrafts/decorative-gift-box.webp'],
            ['Gifts & Handicrafts', 'Terracotta Vase', 'handicraft-terracotta-vase', ['terracotta vase', 'clay handicraft', 'टेराकोटा फूलदान', 'मिट्टी का हस्तशिल्प', 'सजावटी घड़ा'], 'gifts-handicrafts/terracotta-vase.webp'],
            ['Gifts & Handicrafts', 'Carved Wooden Jewelry Box', 'handicraft-wooden-jewelry-box', ['wooden jewelry box', 'carved wooden box', 'लकड़ी का आभूषण बॉक्स', 'नक्काशीदार डिब्बा', 'हस्तशिल्प बॉक्स'], 'gifts-handicrafts/wooden-jewelry-box.webp'],
            ['Gifts & Handicrafts', 'Handwoven Jute Basket', 'handicraft-jute-basket', ['jute basket', 'handwoven basket', 'जूट की टोकरी', 'हाथ से बुनी टोकरी', 'उपहार बास्केट'], 'gifts-handicrafts/jute-basket.webp'],
            ['Gifts & Handicrafts', 'Decorative Floral Candle', 'handicraft-decorative-candle', ['decorative candle', 'floral pillar candle', 'सजावटी मोमबत्ती', 'फूलों वाली कैंडल', 'हस्तनिर्मित मोमबत्ती'], 'gifts-handicrafts/decorative-floral-candle.webp'],
            ['Gifts & Handicrafts', 'Handcrafted Ceramic Vase', 'handicraft-ceramic-vase', ['ceramic vase', 'handmade pottery', 'सिरेमिक फूलदान', 'हस्तनिर्मित पॉटरी', 'सजावटी फूलदान'], 'gifts-handicrafts/ceramic-vase.webp'],
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
