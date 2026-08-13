<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\OrderItem;
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
     */
    public function checkout(User $user, array $items, array $shipping): Order
    {
        return DB::transaction(function () use ($user, $items, $shipping) {

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

                $sellerOrder->update(['subtotal' => $sellerSubtotal]);

                $orderTotal += $sellerSubtotal;

            }

            $order->update([
                'subtotal' => $orderTotal,
                'total' => $orderTotal,
            ]);

            return $order->fresh(['sellerOrders.items', 'sellerOrders.supplier']);

        });
    }
}
