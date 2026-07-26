<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()

            ->count(50)

            ->create()

            ->each(function ($product) {

                $categories = Category::inRandomOrder()

                    ->limit(rand(1,3))

                    ->pluck('id');

                $product

                    ->categories()

                    ->sync($categories);

            });
    }
}
