<?php

namespace App\Services\Inventory;

use App\Models\InventoryTransaction;
use App\Models\ProductVariant;

class InventoryReportService
{
    /**
     * Dashboard summary.
     */
    public function dashboard(): array
    {
        return [

            'total_variants' => ProductVariant::count(),

            'total_stock' => ProductVariant::sum('stock_quantity'),

            'inventory_value' => ProductVariant::selectRaw(
                'SUM(stock_quantity * cost_price) as value'
            )->value('value') ?? 0,

            'low_stock' => ProductVariant::whereColumn(
                'stock_quantity',
                '<=',
                'low_stock_quantity'
            )->count(),

            'out_of_stock' => ProductVariant::where(
                'stock_quantity',
                0
            )->count(),

            'recent_transactions' => InventoryTransaction::count(),

        ];
    }

    /**
     * Low stock variants.
     */
    public function lowStock()
    {
        return ProductVariant::with('product')

            ->whereColumn(
                'stock_quantity',
                '<=',
                'low_stock_quantity'
            )

            ->orderBy('stock_quantity')

            ->paginate(20);
    }

    /**
     * Out of stock variants.
     */
    public function outOfStock()
    {
        return ProductVariant::with('product')

            ->where(
                'stock_quantity',
                0
            )

            ->paginate(20);
    }

    /**
     * Inventory valuation.
     */
    public function inventoryValue()
    {
        return ProductVariant::with('product')

            ->selectRaw('
                *,
                stock_quantity * cost_price AS inventory_value
            ')

            ->orderByDesc('inventory_value')

            ->paginate(20);
    }

    /**
     * Recent inventory transactions.
     */
    public function recentTransactions()
    {
        return InventoryTransaction::with([
            'variant.product',
            'creator'
        ])

        ->latest()

        ->paginate(20);
    }
}
