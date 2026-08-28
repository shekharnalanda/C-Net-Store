<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackThirteenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Medical Supplies', 'First Aid Kit', 'medical-first-aid-kit', ['first aid kit', 'medical box', 'प्राथमिक उपचार किट', 'फर्स्ट एड बॉक्स', 'मेडिकल किट'], 'medical-supplies/first-aid-kit.webp'],
            ['Medical Supplies', 'Digital Thermometer', 'medical-digital-thermometer', ['digital thermometer', 'clinical thermometer', 'डिजिटल थर्मामीटर', 'बुखार नापने की मशीन', 'तापमान मीटर'], 'medical-supplies/digital-thermometer.webp'],
            ['Medical Supplies', 'Blood Pressure Monitor', 'medical-blood-pressure-monitor', ['blood pressure monitor', 'bp machine', 'ब्लड प्रेशर मशीन', 'बीपी मॉनिटर', 'रक्तचाप यंत्र'], 'medical-supplies/blood-pressure-monitor.webp'],
            ['Medical Supplies', 'Protective Face Masks', 'medical-protective-face-masks', ['protective face masks', 'disposable mask', 'फेस मास्क', 'डिस्पोजेबल मास्क', 'सुरक्षा मास्क'], 'medical-supplies/protective-face-masks.webp'],
            ['Medical Supplies', 'Gauze Bandage Roll', 'medical-gauze-bandage-roll', ['gauze bandage roll', 'cotton bandage', 'गॉज पट्टी', 'कॉटन बैंडेज', 'मेडिकल पट्टी'], 'medical-supplies/gauze-bandage-roll.webp'],
            ['Medical Supplies', 'Hot Water Bag', 'medical-hot-water-bag', ['hot water bag', 'rubber heat bag', 'गरम पानी की थैली', 'हॉट वॉटर बैग', 'सिकाई बैग'], 'medical-supplies/hot-water-bag.webp'],
            ['Cleaning Products', 'Floor Cleaning Liquid', 'cleaning-floor-liquid', ['floor cleaning liquid', 'floor cleaner', 'फर्श साफ करने का लिक्विड', 'फ्लोर क्लीनर', 'सफाई द्रव'], 'cleaning-products/floor-cleaning-liquid.webp'],
            ['Cleaning Products', 'Detergent Powder', 'cleaning-detergent-powder', ['detergent powder', 'washing powder', 'डिटर्जेंट पाउडर', 'कपड़े धोने का पाउडर', 'वाशिंग पाउडर'], 'cleaning-products/detergent-powder.webp'],
            ['Cleaning Products', 'Dishwashing Liquid', 'cleaning-dishwashing-liquid', ['dishwashing liquid', 'dish cleaner', 'बर्तन धोने का लिक्विड', 'डिशवॉश', 'बर्तन क्लीनर'], 'cleaning-products/dishwashing-liquid.webp'],
            ['Cleaning Products', 'Toilet Brush', 'cleaning-toilet-brush', ['toilet brush', 'bathroom brush', 'टॉयलेट ब्रश', 'बाथरूम ब्रश', 'शौचालय सफाई ब्रश'], 'cleaning-products/toilet-brush.webp'],
            ['Cleaning Products', 'Broom and Dustpan Set', 'cleaning-broom-dustpan', ['broom and dustpan', 'cleaning broom', 'झाड़ू और डस्टपैन', 'सफाई झाड़ू', 'कूड़ा उठाने का पैन'], 'cleaning-products/broom-dustpan.webp'],
            ['Cleaning Products', 'Cleaning Sponges', 'cleaning-sponges', ['cleaning sponges', 'kitchen scrubber', 'सफाई स्पंज', 'किचन स्क्रबर', 'बर्तन साफ करने का स्पंज'], 'cleaning-products/cleaning-sponges.webp'],
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
