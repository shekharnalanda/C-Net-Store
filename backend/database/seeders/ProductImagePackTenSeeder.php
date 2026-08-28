<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackTenSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Mobile Accessories', 'Phone Charger', 'mobile-phone-charger', ['phone charger', 'mobile charger', 'charging cable', 'मोबाइल चार्जर', 'फोन चार्जर'], 'mobile-accessories/phone-charger.webp'],
            ['Mobile Accessories', 'Wireless Earbuds', 'mobile-wireless-earbuds', ['wireless earbuds', 'bluetooth earbuds', 'tws', 'वायरलेस ईयरबड्स', 'ब्लूटूथ ईयरफोन'], 'mobile-accessories/wireless-earbuds.webp'],
            ['Mobile Accessories', 'Power Bank', 'mobile-power-bank', ['power bank', 'portable charger', 'battery bank', 'पावर बैंक', 'पोर्टेबल चार्जर'], 'mobile-accessories/power-bank.webp'],
            ['Mobile Accessories', 'Mobile Phone Cover', 'mobile-phone-cover', ['mobile cover', 'phone case', 'protective case', 'मोबाइल कवर', 'फोन केस'], 'mobile-accessories/mobile-cover.webp'],
            ['Mobile Accessories', 'Screen Protector', 'mobile-screen-protector', ['screen protector', 'tempered glass', 'mobile glass', 'स्क्रीन प्रोटेक्टर', 'टेम्पर्ड ग्लास'], 'mobile-accessories/screen-protector.webp'],
            ['Mobile Accessories', 'Phone Stand', 'mobile-phone-stand', ['phone stand', 'mobile holder', 'desktop stand', 'मोबाइल स्टैंड', 'फोन होल्डर'], 'mobile-accessories/phone-stand.webp'],
            ['Home Appliances', 'Mixer Grinder', 'appliance-mixer-grinder', ['mixer grinder', 'kitchen mixer', 'मिक्सर ग्राइंडर', 'मिक्सी'], 'home-appliances/mixer-grinder.webp'],
            ['Home Appliances', 'Electric Kettle', 'appliance-electric-kettle', ['electric kettle', 'water kettle', 'इलेक्ट्रिक केतली', 'पानी केतली'], 'home-appliances/electric-kettle.webp'],
            ['Home Appliances', 'Ceiling Fan', 'appliance-ceiling-fan', ['ceiling fan', 'electric fan', 'सीलिंग फैन', 'पंखा'], 'home-appliances/ceiling-fan.webp'],
            ['Home Appliances', 'Electric Iron', 'appliance-electric-iron', ['electric iron', 'steam iron', 'clothes iron', 'इलेक्ट्रिक आयरन', 'प्रेस'], 'home-appliances/electric-iron.webp'],
            ['Home Appliances', 'Water Purifier', 'appliance-water-purifier', ['water purifier', 'ro purifier', 'water filter', 'वाटर प्यूरीफायर', 'आरओ'], 'home-appliances/water-purifier.webp'],
            ['Home Appliances', 'Induction Cooktop', 'appliance-induction-cooktop', ['induction cooktop', 'induction stove', 'electric stove', 'इंडक्शन चूल्हा', 'इंडक्शन कुकटॉप'], 'home-appliances/induction-cooktop.webp'],
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
