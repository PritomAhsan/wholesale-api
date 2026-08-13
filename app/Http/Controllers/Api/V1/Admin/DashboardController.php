<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Api\V1\ApiController;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\Supplier;
use App\Models\User;

class DashboardController extends ApiController
{
    public function index()
    {
        $statistics = [

            'totalProducts' => Product::count(),

            'pendingProducts' => Product::where('status', ProductStatus::PENDING)->count(),

            'totalCategories' => Category::count(),

            'totalBrands' => Brand::count(),

            'totalSuppliers' => Supplier::count(),

            'pendingSuppliers' => Supplier::where('status', 'pending')->count(),

            'totalCustomers' => User::role('Customer')->count(),

            'totalOrders' => Order::count(),

            'totalRevenue' => (float) Order::sum('total'),

            'totalRfqs' => Rfq::count(),

        ];

        $latestProducts = Product::with('supplier')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($product) => [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'supplier' => $product->supplier?->company_name,
                'price' => $product->selling_price,
                'status' => $product->status?->value,
                'image' => optional(
                    $product->images()->where('is_primary', true)->first()
                )?->image_url,
            ]);

        $pendingProducts = Product::with('supplier')
            ->where('status', ProductStatus::PENDING)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($product) => [
                'uuid' => $product->uuid,
                'name' => $product->name,
                'supplier' => $product->supplier?->company_name,
                'category' => null,
                'submitted' => $product->created_at,
            ]);

        $latestSuppliers = Supplier::latest()
            ->take(5)
            ->get()
            ->map(fn ($supplier) => [
                'uuid' => $supplier->uuid,
                'companyName' => $supplier->company_name,
                'businessType' => $supplier->business_type,
                'status' => $supplier->status,
            ]);

        $latestRfqs = Rfq::latest()
            ->take(5)
            ->get()
            ->map(fn ($rfq) => [
                'uuid' => $rfq->uuid,
                'productName' => $rfq->product_name,
                'buyer' => $rfq->contact_name,
                'quantity' => $rfq->quantity,
                'unit' => $rfq->unit,
                'status' => $rfq->status,
            ]);

        $recentOrders = Order::with('user', 'sellerOrders.supplier')
            ->latest('placed_at')
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'uuid' => $order->uuid,
                'orderNumber' => $order->order_number,
                'buyer' => $order->user?->full_name,
                'sellerCount' => $order->sellerOrders->count(),
                'total' => $order->total,
                'status' => $order->status,
                'placedAt' => $order->placed_at,
            ]);

        return $this->success([
            'statistics' => $statistics,
            'latestProducts' => $latestProducts,
            'pendingProducts' => $pendingProducts,
            'latestSuppliers' => $latestSuppliers,
            'latestRfqs' => $latestRfqs,
            'recentOrders' => $recentOrders,
        ]);
    }
}
