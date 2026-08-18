<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'code' => 'PC', 'symbol' => 'pc'],
            ['name' => 'Dozen', 'code' => 'DZ', 'symbol' => 'dz'],
            ['name' => 'Pair', 'code' => 'PR', 'symbol' => 'pr'],
            ['name' => 'Set', 'code' => 'SET', 'symbol' => 'set'],
            ['name' => 'Box', 'code' => 'BOX', 'symbol' => 'box'],
            ['name' => 'Carton', 'code' => 'CTN', 'symbol' => 'ctn'],
            ['name' => 'Pallet', 'code' => 'PLT', 'symbol' => 'plt'],
            ['name' => 'Pack', 'code' => 'PACK', 'symbol' => 'pack'],
            ['name' => 'Bag', 'code' => 'BAG', 'symbol' => 'bag'],
            ['name' => 'Roll', 'code' => 'ROLL', 'symbol' => 'roll'],
            ['name' => 'Kilogram', 'code' => 'KG', 'symbol' => 'kg'],
            ['name' => 'Gram', 'code' => 'G', 'symbol' => 'g'],
            ['name' => 'Ton', 'code' => 'TON', 'symbol' => 't'],
            ['name' => 'Liter', 'code' => 'L', 'symbol' => 'L'],
            ['name' => 'Meter', 'code' => 'M', 'symbol' => 'm'],
            ['name' => 'Square Meter', 'code' => 'SQM', 'symbol' => 'm²'],
        ];

        foreach ($units as $index => $unit) {
            Unit::updateOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'symbol' => $unit['symbol'],
                    'sort_order' => $index,
                    'status' => true,
                ]
            );
        }
    }
}
