<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Inventory\InventoryDashboardResource;
use App\Http\Resources\Inventory\InventorySummaryResource;
use App\Http\Resources\Inventory\InventoryTransactionResource;
use App\Services\Inventory\InventoryReportService;
use Illuminate\Http\JsonResponse;

class InventoryReportController extends ApiController
{
    public function __construct(
        protected InventoryReportService $service
    ) {
    }

    public function dashboard(): JsonResponse
    {
        return $this->success([

            'dashboard' => new InventoryDashboardResource(
                $this->service->dashboard()
            )

        ]);
    }

    public function lowStock(): JsonResponse
    {
        return $this->success([

            'variants' => InventorySummaryResource::collection(
                $this->service->lowStock()
            )

        ]);
    }

    public function outOfStock(): JsonResponse
    {
        return $this->success([

            'variants' => InventorySummaryResource::collection(
                $this->service->outOfStock()
            )

        ]);
    }

    public function inventoryValue(): JsonResponse
    {
        return $this->success([

            'variants' => InventorySummaryResource::collection(
                $this->service->inventoryValue()
            )

        ]);
    }

    public function recentTransactions(): JsonResponse
    {
        return $this->success([

            'transactions' => InventoryTransactionResource::collection(
                $this->service->recentTransactions()
            )

        ]);
    }
}
