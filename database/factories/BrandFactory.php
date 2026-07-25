<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [

            'uuid' => (string) Str::uuid(),

            'name' => $name,

            'slug' => Str::slug($name),

            'description' => fake()->paragraph(),

            'website' => fake()->url(),

            'logo' => null,

            'featured' => fake()->boolean(20),

            'status' => true,

        ];
    }
}
