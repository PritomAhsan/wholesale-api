<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Unit\UnitResource;
use App\Models\Unit;

class PublicUnitController extends ApiController
{
    public function index()
    {
        $units = Unit::active()
            ->ordered()
            ->get();

        return $this->success([
            'units'=>UnitResource::collection($units)
        ]);
    }

    public function show(Unit $unit)
    {
        abort_if(!$unit->status,404);

        return $this->success([
            'unit'=>new UnitResource($unit)
        ]);
    }
}
