<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [

            'uuid' => (string) Str::uuid(),

            'supplier_id' => 1,

            'brand_id' => fake()->numberBetween(1, 20),

            'unit_id' => fake()->numberBetween(1, 14),

            'name' => ucfirst($name),

            'slug' => Str::slug($name),

            'sku' => strtoupper(fake()->bothify('PRD-#####')),

            'short_description' => fake()->sentence(),

            'description' => fake()->paragraphs(3, true),

            'cost_price' => fake()->randomFloat(2, 20, 200),

            'selling_price' => fake()->randomFloat(2, 25, 300),

            'compare_at_price' => fake()->randomFloat(2, 300, 400),

            'currency' => 'USD',

            'min_order_quantity' => 1,

            'stock_quantity' => fake()->numberBetween(0, 500),

            'featured' => fake()->boolean(),

            'status' => ProductStatus::PUBLISHED,

        ];
    }
}
