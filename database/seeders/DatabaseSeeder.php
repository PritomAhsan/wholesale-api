<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            AttributeSeeder::class,
            DemoUserSeeder::class,
            DemoProductSeeder::class,
            DealSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            PayoutSeeder::class,
            DisputeSeeder::class,
            RfqSeeder::class,
            ContactMessageSeeder::class,
            NewsletterSeeder::class,
        ]);
    }
}
