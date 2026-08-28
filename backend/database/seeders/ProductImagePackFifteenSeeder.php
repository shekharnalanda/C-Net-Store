<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackFifteenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Bihar Foods', 'Silao Khaja', 'bihar-silao-khaja', ['silao khaja', 'bihar sweet', 'सिलाव खाजा', 'बिहार की मिठाई', 'खाजा मिठाई'], 'bihar-foods/silao-khaja.webp'],
            ['Bihar Foods', 'Litti Chokha', 'bihar-litti-chokha', ['litti chokha', 'bihar food', 'लिट्टी चोखा', 'बिहारी व्यंजन', 'सत्तू लिट्टी'], 'bihar-foods/litti-chokha.webp'],
            ['Bihar Foods', 'Thekua', 'bihar-thekua', ['thekua', 'bihar snack', 'ठेकुआ', 'बिहारी पकवान', 'छठ प्रसाद'], 'bihar-foods/thekua.webp'],
            ['Bihar Foods', 'Makhana', 'bihar-makhana', ['makhana', 'fox nuts', 'मखाना', 'फॉक्स नट्स', 'बिहार मखाना'], 'bihar-foods/makhana.webp'],
            ['Bihar Foods', 'Sattu', 'bihar-sattu', ['sattu', 'roasted gram flour', 'सत्तू', 'भुना चना आटा', 'बिहार सत्तू'], 'bihar-foods/sattu.webp'],
            ['Bihar Foods', 'Tilkut', 'bihar-tilkut', ['tilkut', 'sesame sweet', 'तिलकुट', 'तिल की मिठाई', 'गया तिलकुट'], 'bihar-foods/tilkut.webp'],
            ['Bihar Handicrafts', 'Madhubani Painting', 'bihar-madhubani-painting', ['madhubani painting', 'mithila art', 'मधुबनी पेंटिंग', 'मिथिला कला', 'बिहार चित्रकला'], 'bihar-handicrafts/madhubani-painting.webp'],
            ['Bihar Handicrafts', 'Sikki Grass Basket', 'bihar-sikki-basket', ['sikki basket', 'grass handicraft', 'सिक्की टोकरी', 'घास हस्तशिल्प', 'बिहार हस्तकला'], 'bihar-handicrafts/sikki-basket.webp'],
            ['Bihar Handicrafts', 'Sujani Cushion Cover', 'bihar-sujani-cushion', ['sujani cushion', 'embroidered cushion', 'सुजनी कुशन', 'सुजनी कढ़ाई', 'बिहार कशीदाकारी'], 'bihar-handicrafts/sujani-cushion.webp'],
            ['Bihar Handicrafts', 'Bhagalpuri Silk Fabric', 'bihar-bhagalpuri-silk', ['bhagalpuri silk', 'tussar silk', 'भागलपुरी सिल्क', 'तसर रेशम', 'बिहार सिल्क'], 'bihar-handicrafts/bhagalpuri-silk.webp'],
            ['Bihar Handicrafts', 'Bamboo Utility Basket', 'bihar-bamboo-basket', ['bamboo basket', 'woven utility basket', 'बांस की टोकरी', 'बुनी टोकरी', 'बिहार बांस शिल्प'], 'bihar-handicrafts/bamboo-basket.webp'],
            ['Bihar Handicrafts', 'Terracotta Horse', 'bihar-terracotta-horse', ['terracotta horse', 'clay folk art', 'टेराकोटा घोड़ा', 'मिट्टी का घोड़ा', 'बिहार लोक कला'], 'bihar-handicrafts/terracotta-horse.webp'],
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
