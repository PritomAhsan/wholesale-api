<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [

            ['Piece','PCS','pc'],
            ['Box','BOX','box'],
            ['Carton','CTN','ctn'],
            ['Kilogram','KG','kg'],
            ['Gram','GM','g'],
            ['Liter','LTR','L'],
            ['Milliliter','ML','ml'],
            ['Meter','MTR','m'],
            ['Centimeter','CM','cm'],
            ['Foot','FT','ft'],
            ['Inch','IN','in'],
            ['Pack','PK','pk'],
            ['Set','SET','set'],
            ['Pair','PAIR','pair'],
            ['Roll','ROLL','roll'],
            ['Pallet','PLT','plt'],

        ];

        foreach ($units as $unit) {

            Unit::updateOrCreate(

                [
                    'code' => $unit[1]
                ],

                [

                    'uuid' => (string) Str::uuid(),

                    'name' => $unit[0],

                    'code' => $unit[1],

                    'symbol' => $unit[2],

                    'status' => true,

                ]

            );

        }
    }
}
