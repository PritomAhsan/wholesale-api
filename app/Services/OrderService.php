<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<int, array{product_uuid: string, quantity: int}>  $items
     * @param  array<string, mixed>  $shipping
     * @param  array{carrier: string, service: string, rate: float}|null  $shippingRate  a live Shippo quote the buyer picked (Phase 18 demo, off by default)
     */
    public function checkout(User $user, array $items, array $shipping, ?array $shippingRate = null): Order
    {
        return DB::transaction(function () use ($user, $items, $shipping, $shippingRate) {

            $uuids = collect($items)->pluck('product_uuid');

            $products = Product::with('supplier', 'primaryImage')
                ->whereIn('uuid', $uuids)
                ->lockForUpdate()
                ->get()
                ->keyBy('uuid');

            $errors = [];
            $bySupplier = [];

            foreach ($items as $item) {

                $product = $products->get($item['product_uuid']);

                if (! $product || $product->status !== ProductStatus::PUBLISHED) {
                    $errors[] = "One or more products are no longer available.";
                    continue;
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < (int) $product->min_order_quantity) {
                    $errors[] = "{$product->name} requires a minimum order quantity of {$product->min_order_quantity}.";
                    continue;
                }

                if ($quantity > $product->stock_quantity) {
                    $errors[] = "{$product->name} only has {$product->stock_quantity} units in stock.";
                    continue;
                }

                $bySupplier[$product->supplier_id][] = [
                    'product' => $product,
                    'quantity' => $quantity,
                ];

            }

            if ($errors) {
                throw ValidationException::withMessages([
                    'items' => $errors,
                ]);
            }

            if (empty($bySupplier)) {
                throw ValidationException::withMessages([
                    'items' => ['Your cart is empty.'],
                ]);
            }

            $orderTotal = 0;

            $order = Order::create([

                'user_id' => $user->id,

                'subtotal' => 0,

                'total' => 0,

                'status' => 'pending',

                'shipping_name' => $shipping['name'],

                'shipping_phone' => $shipping['phone'],

                'shipping_address' => $shipping['address'],

                'shipping_city' => $shipping['city'],

                'shipping_country' => $shipping['country'],

                'shipping_postal_code' => $shipping['postal_code'] ?? null,

                'shipping_cost' => $shippingRate['rate'] ?? null,

                'shipping_carrier' => $shippingRate['carrier'] ?? null,

                'shipping_service' => $shippingRate['service'] ?? null,

                'notes' => $shipping['notes'] ?? null,

                'placed_at' => now(),

            ]);

            foreach ($bySupplier as $supplierId => $lines) {

                $sellerSubtotal = 0;

                $sellerOrder = SellerOrder::create([

                    'seller_order_number' => 'SO-' . strtoupper(Str::random(8)),

                    'order_id' => $order->id,

                    'supplier_id' => $supplierId,

                    'subtotal' => 0,

                    'status' => 'pending',

                ]);

                foreach ($lines as $line) {

                    /** @var Product $product */
                    $product = $line['product'];
                    $quantity = $line['quantity'];
                    $lineTotal = $product->selling_price * $quantity;

                    OrderItem::create([

                        'seller_order_id' => $sellerOrder->id,

                        'product_id' => $product->id,

                        'product_name' => $product->name,

                        'product_sku' => $product->sku,

                        'product_image' => $product->primaryImage?->image_url,

                        'unit_price' => $product->selling_price,

                        'quantity' => $quantity,

                        'line_total' => $lineTotal,

                    ]);

                    $product->decrement('stock_quantity', $quantity);

                    $sellerSubtotal += $lineTotal;

                }

                $rate = $sellerOrder->supplier->effective_commission_rate;

                $commissionAmount = round($sellerSubtotal * $rate / 100, 2);

                $sellerOrder->update([
                    'subtotal' => $sellerSubtotal,
                    'commission_amount' => $commissionAmount,
                    'payable_amount' => $sellerSubtotal - $commissionAmount,
                ]);

                $orderTotal += $sellerSubtotal;

            }

            $order->update([
                'subtotal' => $orderTotal,
                'total' => $orderTotal + ($shippingRate['rate'] ?? 0),
            ]);

            // Scaffolding only — no payment gateway is wired up yet, so
            // this records the amount actually owed without collecting
            // it. Real charging happens once a Stripe integration
            // exists to move this row from 'pending' to 'paid'.
            Payment::create([

                'order_id' => $order->id,

                'amount' => $orderTotal + ($shippingRate['rate'] ?? 0),

                'currency' => 'USD',

                'status' => 'pending',

            ]);

            return $order->fresh(['sellerOrders.items', 'sellerOrders.supplier']);

        });
    }

    /**
     * Cancel an order and every seller order under it, releasing the
     * stock that was reserved at checkout back to each product.
     */
    public function cancel(Order $order, ?User $actor, ?string $reason): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason) {

            $order->loadMissing('sellerOrders.items.product');

            if (! $order->isCancellable()) {
                throw ValidationException::withMessages([
                    'order' => ['This order can no longer be cancelled.'],
                ]);
            }

            foreach ($order->sellerOrders as $sellerOrder) {

                foreach ($sellerOrder->items as $item) {
                    $item->product?->increment('stock_quantity', $item->quantity);
                }

                $sellerOrder->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);

            }

            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $actor?->id,
            ]);

            return $order->fresh(['sellerOrders.items', 'sellerOrders.supplier']);

        });
    }
}
