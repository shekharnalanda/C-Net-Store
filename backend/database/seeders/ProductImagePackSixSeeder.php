<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackSixSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Health & Wellness', 'First Aid Kit', 'first-aid-kit', ['first aid kit', 'medical kit', 'emergency kit', 'फर्स्ट एड किट', 'प्राथमिक उपचार'], 'health-wellness/first-aid-kit.webp'],
            ['Health & Wellness', 'Digital Thermometer', 'digital-thermometer', ['digital thermometer', 'clinical thermometer', 'temperature meter', 'डिजिटल थर्मामीटर', 'तापमान'], 'health-wellness/digital-thermometer.webp'],
            ['Health & Wellness', 'Blood Pressure Monitor', 'blood-pressure-monitor', ['blood pressure monitor', 'bp machine', 'digital bp monitor', 'ब्लड प्रेशर मशीन', 'बीपी मशीन'], 'health-wellness/blood-pressure-monitor.webp'],
            ['Health & Wellness', 'Vitamin Supplements', 'vitamin-supplements', ['vitamin supplements', 'capsules', 'health supplement', 'विटामिन', 'सप्लीमेंट'], 'health-wellness/vitamin-supplements.webp'],
            ['Health & Wellness', 'Protective Face Masks', 'protective-face-masks', ['face mask', 'protective mask', 'disposable mask', 'फेस मास्क', 'सुरक्षा मास्क'], 'health-wellness/protective-face-masks.webp'],
            ['Health & Wellness', 'Hand Sanitizer', 'hand-sanitizer', ['hand sanitizer', 'sanitizer gel', 'hand hygiene', 'हैंड सैनिटाइजर', 'स्वच्छता'], 'health-wellness/hand-sanitizer.webp'],
            ['Books', 'School Textbooks', 'school-textbooks', ['school textbooks', 'textbook', 'study books', 'स्कूल की किताबें', 'पाठ्यपुस्तक'], 'books/school-textbooks.webp'],
            ['Books', 'Story Book', 'story-book', ['story book', 'fiction book', 'illustrated story', 'कहानी की किताब', 'स्टोरी बुक'], 'books/story-book.webp'],
            ['Books', 'Competitive Exam Guide', 'competitive-exam-guide', ['competitive exam guide', 'exam preparation', 'study guide', 'प्रतियोगी परीक्षा', 'एग्जाम गाइड'], 'books/competitive-exam-guide.webp'],
            ['Books', "Children's Picture Book", 'childrens-picture-book', ['children picture book', 'kids book', 'illustrated book', 'बच्चों की किताब', 'चित्र पुस्तक'], 'books/childrens-picture-book.webp'],
            ['Books', 'Cookbook', 'cookbook', ['cookbook', 'recipe book', 'cooking guide', 'कुकबुक', 'रेसिपी किताब'], 'books/cookbook.webp'],
            ['Books', 'General Knowledge Book', 'general-knowledge-book', ['general knowledge book', 'reference book', 'gk book', 'सामान्य ज्ञान', 'जीके बुक'], 'books/general-knowledge-book.webp'],
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
