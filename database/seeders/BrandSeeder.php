<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [

            'Apple',

            'Samsung',

            'Sony',

            'LG',

            'Dell',

            'HP',

            'Asus',

            'Lenovo',

            'Nike',

            'Adidas',

            'Puma',

            'Canon',

            'Epson',

            'Bosch',

            'Philips',

            'Panasonic',

            'Xiaomi',

            'Huawei',

            'Intel',

            'AMD',

        ];

        foreach ($brands as $index => $brand) {

            Brand::updateOrCreate(

                ['name' => $brand],

                [

                    'uuid' => (string) Str::uuid(),

                    'slug' => Str::slug($brand),

                    'logo' => 'seed/logos/l' . ($index % 10 + 1) . '.jpg',

                    'status' => true,

                    'featured' => $index < 6,

                ]

            );

        }
    }
}
