<?php

namespace App\Services\Product;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductApproval;
use App\Models\User;

class ProductApprovalQueryService
{
    /**
     * Approval timeline for a product.
     */
    public function timeline(Product $product)
    {
        return $product->approvals()
            ->with('reviewer')
            ->latest()
            ->get();
    }

    /**
     * Supplier approval history.
     */
    public function supplierHistory(User $supplier)
    {
        return ProductApproval::query()
            ->whereHas('product', function ($query) use ($supplier) {
                $query->where('supplier_id', $supplier->supplier->id);
            })
            ->with(['product', 'reviewer'])
            ->latest()
            ->paginate(20);
    }

    /**
     * Pending approvals.
     */
    public function pending()
    {
        return Product::query()
            ->with(['supplier'])
            ->where('status', ProductStatus::PENDING)
            ->latest()
            ->paginate(20);
    }

    /**
     * Approval statistics.
     */
    public function statistics(): array
    {
        return [

            'pending' => Product::where('status', ProductStatus::PENDING)->count(),

            'approved' => Product::where('status', ProductStatus::APPROVED)->count(),

            'published' => Product::where('status', ProductStatus::PUBLISHED)->count(),

            'rejected' => Product::where('status', ProductStatus::REJECTED)->count(),

            'draft' => Product::where('status', ProductStatus::DRAFT)->count(),

            'archived' => Product::where('status', ProductStatus::ARCHIVED)->count(),

        ];
    }
}
