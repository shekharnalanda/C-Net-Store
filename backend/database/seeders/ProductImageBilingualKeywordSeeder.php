<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageBilingualKeywordSeeder extends Seeder
{
    public function run(): void
    {
        $groupKeywords = [
            'Food' => 'भोजन',
            'Grocery' => 'किराना',
            'Fruits' => 'फल',
            'Fruit' => 'फल',
            'Vegetables' => 'सब्जियां',
            'Vegetable' => 'सब्जी',
            'Beauty' => 'सौंदर्य',
            'Beauty & Personal Care' => 'सौंदर्य प्रसाधन',
            'Personal Care' => 'व्यक्तिगत देखभाल',
            'Cosmetics' => 'सौंदर्य प्रसाधन',
            'Electronics' => 'इलेक्ट्रॉनिक्स',
            'Mobile' => 'मोबाइल',
            'Mobile Accessories' => 'मोबाइल सामान',
            'Computers' => 'कंप्यूटर',
            'Home Appliances' => 'घरेलू उपकरण',
            'Kitchen Appliances' => 'रसोई उपकरण',
            'Fashion' => 'फैशन',
            'Clothing' => 'कपड़े',
            'Men Fashion' => 'पुरुष परिधान',
            'Women Fashion' => 'महिला परिधान',
            'Kids Fashion' => 'बच्चों के कपड़े',
            'Footwear' => 'जूते चप्पल',
            'Bags' => 'बैग',
            'Bags & Luggage' => 'बैग और सामान',
            'Furniture' => 'फर्नीचर',
            'Home & Kitchen' => 'घर और रसोई',
            'Medicine' => 'दवा',
            'Health' => 'स्वास्थ्य',
            'Baby Care' => 'शिशु देखभाल',
            'Books' => 'किताबें',
            'Stationery' => 'स्टेशनरी',
            'Sports' => 'खेल सामग्री',
            'Toys' => 'खिलौने',
            'Automotive' => 'वाहन सामग्री',
            'Agriculture' => 'कृषि सामग्री',
            'Construction' => 'निर्माण सामग्री',
            'Construction Materials' => 'निर्माण सामग्री',
            'Hardware' => 'हार्डवेयर',
            'Pet Supplies' => 'पालतू पशु सामग्री',
        ];

        $updated = 0;

        DB::table('product_image_assets')
            ->orderBy('id')
            ->get()
            ->each(function ($asset) use ($groupKeywords, &$updated): void {
                $keywords = json_decode($asset->keywords ?: '[]', true);

                if (! is_array($keywords)) {
                    $keywords = [];
                }

                $hasHindi = preg_match(
                    '/\p{Devanagari}/u',
                    implode(' ', $keywords)
                ) === 1;

                if ($hasHindi) {
                    return;
                }

                $hindiKeyword = $groupKeywords[$asset->group_name] ?? 'उत्पाद';
                $keywords[] = $hindiKeyword;
                $keywords = array_values(array_unique(array_filter($keywords)));

                DB::table('product_image_assets')
                    ->where('id', $asset->id)
                    ->update([
                        'keywords' => json_encode(
                            $keywords,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ),
                        'updated_at' => now(),
                    ]);

                $updated++;
            });

        $this->command?->info(
            "BILINGUAL_IMAGE_KEYWORDS_UPDATED={$updated}"
        );
    }
}
