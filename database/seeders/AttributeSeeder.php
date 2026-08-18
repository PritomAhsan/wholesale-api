<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $color = Attribute::updateOrCreate(
            ['name' => 'Color'],
            ['type' => 'select', 'is_filterable' => true, 'status' => true]
        );

        foreach([
            'Black',
            'White',
            'Red',
            'Blue',
            'Green'
        ] as $value){

            AttributeValue::firstOrCreate([

                'attribute_id'=>$color->id,

                'value'=>$value,

            ]);

        }

        $size = Attribute::updateOrCreate(
            ['name' => 'Size'],
            ['type' => 'select', 'is_filterable' => true, 'status' => true]
        );

        foreach([
            'S',
            'M',
            'L',
            'XL',
            'XXL'
        ] as $value){

            AttributeValue::firstOrCreate([

                'attribute_id'=>$size->id,

                'value'=>$value,

            ]);

        }
    }
}
