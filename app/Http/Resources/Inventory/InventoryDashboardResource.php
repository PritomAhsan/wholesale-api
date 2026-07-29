<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'total_variants' => $this['total_variants'],

            'total_stock' => $this['total_stock'],

            'inventory_value' => $this['inventory_value'],

            'low_stock' => $this['low_stock'],

            'out_of_stock' => $this['out_of_stock'],

            'recent_transactions' => $this['recent_transactions'],

        ];
    }
}
