<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Product;
use App\Models\SellerOrder;
use Illuminate\Http\Request;

class SupplierDashboardController extends ApiController
{
    public function index(Request $request)
    {
        $supplier = $request->user()->supplier;

        if (! $supplier) {
            return $this->error('No supplier account found.', null, 403);
        }

        $products = Product::where('supplier_id', $supplier->id);

        $sellerOrders = SellerOrder::where('supplier_id', $supplier->id);

        $statistics = [

            'totalProducts' => (clone $products)->count(),

            'publishedProducts' => (clone $products)->where('status', ProductStatus::PUBLISHED)->count(),

            'pendingProducts' => (clone $products)->where('status', ProductStatus::PENDING)->count(),

            'totalOrders' => (clone $sellerOrders)->count(),

            'pendingOrders' => (clone $sellerOrders)->where('status', 'pending')->count(),

            'totalRevenue' => (float) (clone $sellerOrders)->sum('subtotal'),

        ];

        $latestProducts = Product::where('supplier_id', $supplier->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($product) => [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'price' => $product->selling_price,
                'status' => $product->status?->value,
                'image' => optional(
                    $product->images()->where('is_primary', true)->first()
                )?->image_url,
            ]);

        $recentOrders = SellerOrder::with('order')
            ->where('supplier_id', $supplier->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($sellerOrder) => [
                'uuid' => $sellerOrder->uuid,
                'sellerOrderNumber' => $sellerOrder->seller_order_number,
                'shipTo' => $sellerOrder->order?->shipping_city . ', ' . $sellerOrder->order?->shipping_country,
                'total' => $sellerOrder->subtotal,
                'status' => $sellerOrder->status,
                'placedAt' => $sellerOrder->order?->placed_at,
            ]);

        return $this->success([
            'statistics' => $statistics,
            'latestProducts' => $latestProducts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
