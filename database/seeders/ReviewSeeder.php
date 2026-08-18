<?php

namespace Database\Seeders;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Models\StoreFollow;
use App\Models\StoreReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    private const COMMENTS = [
        'Great quality for the price, will reorder for our next shipment.',
        'Packaging was solid and the batch matched the sample exactly.',
        'Delivery took a bit longer than expected but the product itself is excellent.',
        'Consistent quality across the whole bulk order. Very satisfied.',
        'Exactly as described. Communication with the seller was smooth throughout.',
        'Good value for wholesale pricing, minor variance in a few units.',
        'Fast dispatch and everything arrived in perfect condition.',
        'Solid supplier — this is our third order from them.',
    ];

    public function run(): void
    {
        $deliveredItems = OrderItem::whereHas('sellerOrder', fn ($q) => $q->where('status', 'delivered'))
            ->with(['sellerOrder.order.user', 'sellerOrder.supplier'])
            ->get();

        $reviewedProductUser = [];
        $reviewedStoreUser = [];

        foreach ($deliveredItems as $item) {
            $sellerOrder = $item->sellerOrder;
            $order = $sellerOrder?->order;
            if (! $order || ! $order->user) {
                continue;
            }

            $user = $order->user;

            // Product review — ~65% of delivered items get one, one per (product, user).
            $productKey = $item->product_id . ':' . $user->id;
            if (mt_rand(1, 100) <= 65 && ! isset($reviewedProductUser[$productKey])) {
                $reviewedProductUser[$productKey] = true;

                Review::updateOrCreate(
                    ['product_id' => $item->product_id, 'user_id' => $user->id],
                    [
                        'order_item_id' => $item->id,
                        'rating' => [3, 4, 4, 5, 5, 5][array_rand([3, 4, 4, 5, 5, 5])],
                        'comment' => self::COMMENTS[array_rand(self::COMMENTS)],
                        'status' => 'approved',
                    ]
                );
            }

            // Store review — ~50% of delivered seller orders, one per (supplier, user).
            $storeKey = $sellerOrder->supplier_id . ':' . $user->id;
            if (mt_rand(1, 100) <= 50 && ! isset($reviewedStoreUser[$storeKey])) {
                $reviewedStoreUser[$storeKey] = true;

                StoreReview::updateOrCreate(
                    ['supplier_id' => $sellerOrder->supplier_id, 'user_id' => $user->id],
                    [
                        'seller_order_id' => $sellerOrder->id,
                        'rating' => [3, 4, 4, 5, 5][array_rand([3, 4, 4, 5, 5])],
                        'communication_rating' => rand(3, 5),
                        'shipping_rating' => rand(3, 5),
                        'packaging_rating' => rand(3, 5),
                        'comment' => self::COMMENTS[array_rand(self::COMMENTS)],
                        'status' => 'approved',
                    ]
                );
            }
        }

        // Follows — every customer follows a couple of suppliers they've bought from,
        // plus a few browsing-only follows.
        $customers = User::role('Customer')->get();
        $supplierIds = SellerOrder::pluck('supplier_id')->unique();

        foreach ($customers as $customer) {
            $followCount = rand(0, 3);
            if ($followCount === 0) {
                continue;
            }
            foreach ($supplierIds->random(min($followCount, $supplierIds->count())) as $supplierId) {
                StoreFollow::firstOrCreate([
                    'user_id' => $customer->id,
                    'supplier_id' => $supplierId,
                ]);
            }
        }
    }
}
