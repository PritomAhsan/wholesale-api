<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Electronics & Electrical' => ['Consumer Electronics', 'Mobile Accessories', 'Cables & Adapters', 'Home Appliances'],
            'Industrial & Machinery' => ['Power Tools', 'Hand Tools', 'Safety Equipment', 'Fasteners & Hardware'],
            'Apparel & Textiles' => ['Workwear', 'Fabrics & Textiles', 'Footwear', 'Bags & Luggage'],
            'Home & Garden' => ['Kitchenware', 'Furniture', 'Cleaning Supplies', 'Garden Tools'],
            'Packaging & Printing' => ['Packaging Materials', 'Labels & Stickers', 'Printing Supplies'],
            'Office & School Supplies' => ['Stationery', 'Office Furniture', 'Printers & Consumables'],
            'Beauty & Personal Care' => ['Skincare', 'Haircare', 'Personal Hygiene'],
            'Food & Beverage' => ['Snacks & Confectionery', 'Beverages', 'Bulk Ingredients'],
        ];

        $imageIndex = 1;
        $sort = 0;

        foreach ($tree as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentName)],
                [
                    'name' => $parentName,
                    'description' => "Wholesale {$parentName} sourced directly from verified suppliers.",
                    'image' => 'seed/categories/c' . (($imageIndex - 1) % 10 + 1) . '.jpg',
                    'sort_order' => $sort++,
                    'status' => true,
                ]
            );
            $imageIndex++;

            foreach ($children as $i => $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childName,
                        'description' => "Bulk {$childName} for B2B buyers.",
                        'image' => 'seed/categories/c' . (($imageIndex - 1) % 10 + 1) . '.jpg',
                        'sort_order' => $i,
                        'status' => true,
                    ]
                );
                $imageIndex++;
            }
        }
    }
}
