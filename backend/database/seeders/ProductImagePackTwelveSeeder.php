<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackTwelveSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Agriculture', 'Crop Seed Packet', 'agri-crop-seed-packet', ['crop seed packet', 'farm seeds', 'बीज पैकेट', 'फसल बीज', 'खेती का बीज'], 'agriculture/crop-seed-packet.webp'],
            ['Agriculture', 'Fertilizer Bag', 'agri-fertilizer-bag', ['fertilizer bag', 'farm fertilizer', 'खाद की बोरी', 'उर्वरक', 'खेती की खाद'], 'agriculture/fertilizer-bag.webp'],
            ['Agriculture', 'Manual Pressure Sprayer', 'agri-manual-pressure-sprayer', ['manual pressure sprayer', 'farm sprayer', 'स्प्रे मशीन', 'कृषि स्प्रेयर', 'दवा छिड़काव'], 'agriculture/manual-pressure-sprayer.webp'],
            ['Agriculture', 'Farming Sickle', 'agri-farming-sickle', ['farming sickle', 'harvesting sickle', 'हंसिया', 'दरांती', 'फसल काटने का औजार'], 'agriculture/farming-sickle.webp'],
            ['Agriculture', 'Irrigation Pipe Roll', 'agri-irrigation-pipe-roll', ['irrigation pipe roll', 'farm water pipe', 'सिंचाई पाइप', 'खेती का पाइप', 'पानी पाइप रोल'], 'agriculture/irrigation-pipe-roll.webp'],
            ['Agriculture', 'Animal Feed Sack', 'agri-animal-feed-sack', ['animal feed sack', 'cattle feed', 'पशु आहार', 'मवेशी चारा', 'दाना बोरी'], 'agriculture/animal-feed-sack.webp'],
            ['Construction Materials', 'Cement Bag', 'construction-cement-bag', ['cement bag', 'construction cement', 'सीमेंट की बोरी', 'निर्माण सीमेंट', 'बिल्डिंग सामग्री'], 'construction-materials/cement-bag.webp'],
            ['Construction Materials', 'Red Bricks', 'construction-red-bricks', ['red bricks', 'clay bricks', 'लाल ईंट', 'निर्माण ईंट', 'बिल्डिंग ईंट'], 'construction-materials/red-bricks.webp'],
            ['Construction Materials', 'Steel Rebar Bundle', 'construction-steel-rebar', ['steel rebar', 'reinforcement bars', 'सरिया', 'स्टील छड़', 'निर्माण सरिया'], 'construction-materials/steel-rebar.webp'],
            ['Construction Materials', 'Ceramic Floor Tiles', 'construction-ceramic-tiles', ['ceramic floor tiles', 'marble tiles', 'फर्श टाइल्स', 'सिरेमिक टाइल', 'मार्बल टाइल'], 'construction-materials/ceramic-tiles.webp'],
            ['Construction Materials', 'PVC Plumbing Pipes', 'construction-pvc-pipes', ['pvc plumbing pipes', 'water pipe', 'पीवीसी पाइप', 'प्लंबिंग पाइप', 'पानी का पाइप'], 'construction-materials/pvc-pipes.webp'],
            ['Construction Materials', 'Concrete Blocks', 'construction-concrete-blocks', ['concrete blocks', 'masonry blocks', 'कंक्रीट ब्लॉक', 'सीमेंट ब्लॉक', 'निर्माण ब्लॉक'], 'construction-materials/concrete-blocks.webp'],
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
