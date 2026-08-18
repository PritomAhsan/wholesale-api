<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('status', 'published')->inRandomOrder()->take(15)->get();

        if ($products->isEmpty()) {
            return;
        }

        $chunks = $products->chunk((int) ceil($products->count() / 3));

        // Flash deals — time-limited percentage discounts.
        foreach ($chunks->get(0, collect()) as $product) {
            Deal::updateOrCreate(
                ['product_id' => $product->id, 'type' => 'flash'],
                [
                    'title' => 'Flash Sale — ' . $product->name,
                    'description' => 'Limited-time discount on this listing.',
                    'discount_percent' => [10, 15, 20, 25][array_rand([10, 15, 20, 25])],
                    'min_quantity' => null,
                    'starts_at' => now()->subDays(1),
                    'ends_at' => now()->addDays(rand(3, 14)),
                    'status' => 'active',
                ]
            );
        }

        // Bulk quantity-break deals.
        foreach ($chunks->get(1, collect()) as $product) {
            Deal::updateOrCreate(
                ['product_id' => $product->id, 'type' => 'bulk'],
                [
                    'title' => 'Bulk Pricing — ' . $product->name,
                    'description' => 'Order in bulk to unlock a lower unit price.',
                    'discount_percent' => [12, 18, 22][array_rand([12, 18, 22])],
                    'min_quantity' => [50, 100, 200][array_rand([50, 100, 200])],
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
        }

        // Clearance deals — open-ended, no end date.
        foreach ($chunks->get(2, collect()) as $product) {
            Deal::updateOrCreate(
                ['product_id' => $product->id, 'type' => 'clearance'],
                [
                    'title' => 'Clearance — ' . $product->name,
                    'description' => 'Remaining stock priced to clear.',
                    'discount_percent' => [30, 35, 40][array_rand([30, 35, 40])],
                    'min_quantity' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}
