<?php

namespace Database\Seeders;

use App\Models\ProductImageAsset;
use Illuminate\Database\Seeder;

class ProductImagePackEightSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['Pet Supplies', 'Pet Food Bowl', 'pet-food-bowl', ['pet food bowl', 'dog bowl', 'cat bowl', 'पालतू भोजन कटोरा', 'पेट बाउल'], 'pet-supplies/pet-food-bowl.webp'],
            ['Pet Supplies', 'Dog Collar and Leash', 'dog-collar-leash', ['dog collar', 'dog leash', 'pet belt', 'कुत्ते का पट्टा', 'डॉग कॉलर'], 'pet-supplies/dog-collar-leash.webp'],
            ['Pet Supplies', 'Pet Bed', 'pet-bed', ['pet bed', 'dog bed', 'cat bed', 'पालतू बिस्तर', 'पेट बेड'], 'pet-supplies/pet-bed.webp'],
            ['Pet Supplies', 'Cat Litter Tray', 'cat-litter-tray', ['cat litter tray', 'litter box', 'cat toilet', 'कैट लिटर ट्रे', 'बिल्ली ट्रे'], 'pet-supplies/cat-litter-tray.webp'],
            ['Pet Supplies', 'Bird Cage', 'bird-cage', ['bird cage', 'pet cage', 'bird house', 'पक्षी पिंजरा', 'बर्ड केज'], 'pet-supplies/bird-cage.webp'],
            ['Pet Supplies', 'Pet Grooming Brush', 'pet-grooming-brush', ['pet grooming brush', 'dog brush', 'cat brush', 'पेट ग्रूमिंग ब्रश', 'बाल ब्रश'], 'pet-supplies/pet-grooming-brush.webp'],
            ['Hardware & Tools', 'Claw Hammer', 'claw-hammer', ['claw hammer', 'steel hammer', 'hand tool', 'हथौड़ा', 'क्लॉ हैमर'], 'hardware-tools/claw-hammer.webp'],
            ['Hardware & Tools', 'Screwdriver Set', 'screwdriver-set', ['screwdriver set', 'flat screwdriver', 'phillips screwdriver', 'पेचकस सेट', 'स्क्रूड्राइवर'], 'hardware-tools/screwdriver-set.webp'],
            ['Hardware & Tools', 'Combination Pliers', 'combination-pliers', ['combination pliers', 'cutting pliers', 'hand tool', 'प्लास', 'कॉम्बिनेशन प्लायर'], 'hardware-tools/combination-pliers.webp'],
            ['Hardware & Tools', 'Cordless Drill', 'cordless-drill', ['cordless drill', 'electric drill', 'drill machine', 'ड्रिल मशीन', 'कॉर्डलेस ड्रिल'], 'hardware-tools/cordless-drill.webp'],
            ['Hardware & Tools', 'Measuring Tape', 'measuring-tape', ['measuring tape', 'tape measure', 'measurement tool', 'मापने का फीता', 'मेजरिंग टेप'], 'hardware-tools/measuring-tape.webp'],
            ['Hardware & Tools', 'Paint Brush and Roller Set', 'paint-brush-roller-set', ['paint brush', 'paint roller', 'painting set', 'पेंट ब्रश', 'पेंट रोलर'], 'hardware-tools/paint-brush-roller-set.webp'],
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
