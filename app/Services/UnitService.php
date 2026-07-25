<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UnitService
{
    public function create(array $data): Unit
    {
        return DB::transaction(function () use ($data) {

            return Unit::create([

                'uuid' => (string) Str::uuid(),

                'name' => $data['name'],

                'code' => strtoupper($data['code']),

                'symbol' => $data['symbol'] ?? null,

                'description' => $data['description'] ?? null,

                'sort_order' => $data['sort_order'] ?? 0,

                'status' => $data['status'] ?? true,

            ]);

        });
    }

    public function update(Unit $unit, array $data): Unit
    {
        return DB::transaction(function () use ($unit, $data) {

            $unit->update([

                'name' => $data['name'],

                'code' => strtoupper($data['code']),

                'symbol' => $data['symbol'] ?? null,

                'description' => $data['description'] ?? null,

                'sort_order' => $data['sort_order'] ?? 0,

                'status' => $data['status'] ?? true,

            ]);

            return $unit->fresh();

        });
    }

    public function delete(Unit $unit): void
    {
        $unit->delete();
    }

    public function restore(string $uuid): Unit
    {
        $unit = Unit::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $unit->restore();

        return $unit;
    }

    public function forceDelete(string $uuid): void
    {
        $unit = Unit::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $unit->forceDelete();
    }

    public function toggleStatus(Unit $unit): Unit
    {
        $unit->update([
            'status' => ! $unit->status,
        ]);

        return $unit->fresh();
    }
}
