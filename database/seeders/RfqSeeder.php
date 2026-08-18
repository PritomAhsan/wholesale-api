<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Rfq;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class RfqSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('Customer')->get();
        $products = Product::where('status', 'published')->inRandomOrder()->take(10)->get();
        $admin = User::role('Admin')->first();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'pending', 'quoted', 'quoted', 'accepted', 'rejected', 'closed'];

        foreach ($products as $i => $product) {
            $customer = $customers->random();
            $status = $statuses[$i % count($statuses)];
            $supplier = Supplier::find($product->supplier_id);

            $rfq = Rfq::create([
                'user_id' => $customer->id,
                'supplier_id' => $product->supplier_id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'preferred_supplier_name' => $supplier?->display_name,
                'quantity' => [100, 250, 500, 1000, 2000][array_rand([100, 250, 500, 1000, 2000])],
                'unit' => 'Pieces',
                'budget' => round((float) $product->selling_price * rand(80, 400), 2),
                'destination_country' => ['USA', 'UK', 'UAE', 'Canada', 'Australia'][array_rand(['USA', 'UK', 'UAE', 'Canada', 'Australia'])],
                'required_delivery_date' => now()->addDays(rand(15, 60))->toDateString(),
                'message' => "Requesting a quote for bulk purchase of {$product->name}. Please include lead time and shipping terms.",
                'admin_response' => in_array($status, ['quoted', 'accepted', 'rejected', 'closed'], true)
                    ? 'Quote sent based on current stock and requested delivery window.'
                    : null,
                'contact_name' => $customer->full_name,
                'contact_email' => $customer->email,
                'contact_phone' => '+1' . rand(2000000000, 9999999999),
                'status' => $status,
                'responded_at' => in_array($status, ['quoted', 'accepted', 'rejected', 'closed'], true)
                    ? now()->subDays(rand(1, 10))
                    : null,
                'responded_by' => in_array($status, ['quoted', 'accepted', 'rejected', 'closed'], true)
                    ? $admin?->id
                    : null,
            ]);
        }
    }
}
