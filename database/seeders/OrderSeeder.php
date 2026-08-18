<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::role('Customer')->get();
        $products = Product::where('status', 'published')->with('supplier')->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        $statusWeights = array_merge(
            array_fill(0, 3, 'pending'),
            array_fill(0, 3, 'processing'),
            array_fill(0, 22, 'completed'),
            array_fill(0, 2, 'cancelled'),
        );

        $cities = [
            ['New York', 'USA'], ['London', 'UK'], ['Toronto', 'Canada'],
            ['Sydney', 'Australia'], ['Dubai', 'UAE'], ['Singapore', 'Singapore'],
            ['Berlin', 'Germany'], ['Lagos', 'Nigeria'], ['Mumbai', 'India'],
            ['Sao Paulo', 'Brazil'],
        ];

        $orderCount = 30;

        for ($i = 0; $i < $orderCount; $i++) {
            $customer = $customers->random();
            $orderStatus = $statusWeights[array_rand($statusWeights)];
            [$city, $country] = $cities[array_rand($cities)];
            $placedAt = now()->subDays(rand(1, 120));

            // 1–3 products, possibly across different suppliers, split
            // into per-supplier seller orders like a real checkout.
            $lineCount = rand(1, 3);
            $chosenProducts = $products->random(min($lineCount, $products->count()));
            if (! $chosenProducts instanceof \Illuminate\Support\Collection) {
                $chosenProducts = collect([$chosenProducts]);
            }

            $bySupplier = $chosenProducts->groupBy(fn ($p) => $p->supplier_id);

            $orderSubtotal = 0;
            $lines = [];

            foreach ($bySupplier as $supplierId => $supplierProducts) {
                $supplierLines = [];
                foreach ($supplierProducts as $product) {
                    $qty = max((int) $product->min_order_quantity, rand(5, 100));
                    $unitPrice = (float) $product->selling_price;
                    $lineTotal = round($unitPrice * $qty, 2);
                    $orderSubtotal += $lineTotal;
                    $supplierLines[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                }
                $lines[$supplierId] = $supplierLines;
            }

            $order = Order::create([
                'user_id' => $customer->id,
                'subtotal' => $orderSubtotal,
                'total' => $orderSubtotal,
                'currency' => 'USD',
                'status' => $orderStatus,
                'shipping_name' => $customer->full_name,
                'shipping_phone' => '+1' . rand(2000000000, 9999999999),
                'shipping_address' => rand(100, 9999) . ' ' . ['Market St', 'Industrial Ave', 'Commerce Blvd', 'Trade Way'][array_rand(['Market St', 'Industrial Ave', 'Commerce Blvd', 'Trade Way'])],
                'shipping_city' => $city,
                'shipping_country' => $country,
                'shipping_postal_code' => (string) rand(10000, 99999),
                'notes' => null,
                'placed_at' => $placedAt,
            ]);

            $sellerOrderStatus = match ($orderStatus) {
                'pending' => 'pending',
                'processing' => ['processing', 'shipped'][array_rand(['processing', 'shipped'])],
                'cancelled' => 'cancelled',
                default => 'delivered',
            };

            foreach ($lines as $supplierId => $supplierLines) {
                $supplier = Supplier::find($supplierId);
                $subtotal = collect($supplierLines)->sum('line_total');
                $rate = $supplier->effective_commission_rate;
                $commissionAmount = round($subtotal * $rate / 100, 2);
                $payableAmount = round($subtotal - $commissionAmount, 2);

                $shippedAt = in_array($sellerOrderStatus, ['shipped', 'delivered'], true)
                    ? $placedAt->copy()->addDays(rand(1, 3))
                    : null;
                $deliveredAt = $sellerOrderStatus === 'delivered'
                    ? $placedAt->copy()->addDays(rand(4, 10))
                    : null;

                $sellerOrder = SellerOrder::create([
                    'seller_order_number' => 'SO-' . strtoupper(\Illuminate\Support\Str::random(8)),
                    'order_id' => $order->id,
                    'supplier_id' => $supplierId,
                    'subtotal' => $subtotal,
                    'commission_amount' => $sellerOrderStatus === 'cancelled' ? null : $commissionAmount,
                    'payable_amount' => $sellerOrderStatus === 'cancelled' ? null : $payableAmount,
                    'status' => $sellerOrderStatus,
                    'tracking_number' => $shippedAt ? 'TRK' . strtoupper(substr(md5($order->order_number . $supplierId), 0, 10)) : null,
                    'shipping_carrier' => $shippedAt ? ['DHL', 'FedEx', 'UPS', 'Maersk Line'][array_rand(['DHL', 'FedEx', 'UPS', 'Maersk Line'])] : null,
                    'shipped_at' => $shippedAt,
                    'delivered_at' => $deliveredAt,
                ]);

                foreach ($supplierLines as $line) {
                    $product = $line['product'];
                    $primaryImage = $product->images()->where('is_primary', true)->first();

                    OrderItem::create([
                        'seller_order_id' => $sellerOrder->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_image' => $primaryImage?->image,
                        'unit_price' => $line['unit_price'],
                        'quantity' => $line['qty'],
                        'line_total' => $line['line_total'],
                    ]);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'amount' => $orderSubtotal,
                'currency' => 'USD',
                'method' => ['bank_transfer', 'card', 'wire'][array_rand(['bank_transfer', 'card', 'wire'])],
                'status' => 'pending',
                'paid_at' => null,
            ]);
        }
    }
}
