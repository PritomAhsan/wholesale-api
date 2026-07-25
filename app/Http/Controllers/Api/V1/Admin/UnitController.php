<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Unit\UnitRequest;
use App\Http\Resources\Unit\UnitResource;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;

class UnitController extends ApiController
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    public function index(Request $request)
    {
        $units = Unit::query()

            ->when(
                $request->filled('search'),
                fn($q) => $q->where('name','like','%'.$request->search.'%')
                    ->orWhere('code','like','%'.$request->search.'%')
            )

            ->when(
                $request->filled('status'),
                fn($q) => $q->where(
                    'status',
                    $request->boolean('status')
                )
            )

            ->orderBy('sort_order')
            ->paginate(
                $request->integer('per_page',15)
            );

        return $this->success([
            'units'=>UnitResource::collection($units),
            'pagination'=>[
                'current_page'=>$units->currentPage(),
                'last_page'=>$units->lastPage(),
                'per_page'=>$units->perPage(),
                'total'=>$units->total(),
            ]
        ]);
    }

    public function store(UnitRequest $request)
    {
        $unit = $this->unitService
            ->create($request->validated());

        return $this->success([
            'unit'=>new UnitResource($unit)
        ],'Unit created successfully.',201);
    }

    public function show(Unit $unit)
    {
        return $this->success([
            'unit'=>new UnitResource($unit)
        ]);
    }

    public function update(
        UnitRequest $request,
        Unit $unit
    ) {

        $unit = $this->unitService
            ->update(
                $unit,
                $request->validated()
            );

        return $this->success([
            'unit'=>new UnitResource($unit)
        ],'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        if($unit->products()->exists()){

            return $this->error(
                'Unit is being used by products.',
                [],
                422
            );

        }

        $this->unitService->delete($unit);

        return $this->success(
            null,
            'Unit deleted successfully.'
        );
    }

    public function restore(string $uuid)
    {
        $unit = $this->unitService
            ->restore($uuid);

        return $this->success([
            'unit'=>new UnitResource($unit)
        ]);
    }

    public function forceDelete(string $uuid)
    {
        $this->unitService
            ->forceDelete($uuid);

        return $this->success(
            null,
            'Unit permanently deleted.'
        );
    }

    public function toggleStatus(Unit $unit)
    {
        $unit = $this->unitService
            ->toggleStatus($unit);

        return $this->success([
            'unit'=>new UnitResource($unit)
        ]);
    }
}
