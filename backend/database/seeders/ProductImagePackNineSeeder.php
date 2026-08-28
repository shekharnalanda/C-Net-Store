<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackNineSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Garden & Outdoor', 'Watering Can', 'watering-can', ['watering can', 'garden water can', 'plant watering', 'पानी का कैन', 'वॉटरिंग कैन'], 'garden-outdoor/watering-can.webp'],
            ['Garden & Outdoor', 'Garden Hand Tools', 'garden-hand-tools', ['garden hand tools', 'trowel', 'hand fork', 'gardening tools', 'बागवानी औजार', 'खुरपी'], 'garden-outdoor/garden-hand-tools.webp'],
            ['Garden & Outdoor', 'Plant Pot', 'plant-pot', ['plant pot', 'flower pot', 'terracotta pot', 'गमला', 'फूल का गमला'], 'garden-outdoor/plant-pot.webp'],
            ['Garden & Outdoor', 'Garden Hose', 'garden-hose', ['garden hose', 'water hose', 'spray nozzle', 'पानी पाइप', 'गार्डन होज'], 'garden-outdoor/garden-hose.webp'],
            ['Garden & Outdoor', 'Outdoor Chair', 'outdoor-chair', ['outdoor chair', 'garden chair', 'patio chair', 'गार्डन कुर्सी', 'आउटडोर चेयर'], 'garden-outdoor/outdoor-chair.webp'],
            ['Garden & Outdoor', 'Pruning Shears', 'pruning-shears', ['pruning shears', 'garden cutter', 'plant cutter', 'छंटाई कैंची', 'गार्डन कैंची'], 'garden-outdoor/pruning-shears.webp'],
            ['Office Supplies', 'Notebook and Pen', 'notebook-pen', ['notebook and pen', 'office notebook', 'diary pen', 'नोटबुक और पेन', 'ऑफिस डायरी'], 'office-supplies/notebook-pen.webp'],
            ['Office Supplies', 'File Folders', 'file-folders', ['file folders', 'document folder', 'office file', 'फाइल फोल्डर', 'दस्तावेज फाइल'], 'office-supplies/file-folders.webp'],
            ['Office Supplies', 'Desk Organizer', 'desk-organizer', ['desk organizer', 'pen stand', 'office organizer', 'डेस्क ऑर्गनाइजर', 'पेन स्टैंड'], 'office-supplies/desk-organizer.webp'],
            ['Office Supplies', 'Stapler', 'stapler', ['stapler', 'office stapler', 'paper stapler', 'स्टेपलर', 'कागज स्टेपलर'], 'office-supplies/stapler.webp'],
            ['Office Supplies', 'Office Calculator', 'office-calculator', ['calculator', 'desktop calculator', 'office calculator', 'कैलकुलेटर', 'गणना मशीन'], 'office-supplies/calculator.webp'],
            ['Office Supplies', 'Office Chair', 'office-chair', ['office chair', 'ergonomic chair', 'computer chair', 'ऑफिस कुर्सी', 'कंप्यूटर चेयर'], 'office-supplies/office-chair.webp'],
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
