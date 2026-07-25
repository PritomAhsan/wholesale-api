<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * Create Brand
     */
    public function create(array $data): Brand
    {
        return DB::transaction(function () use ($data) {

            $logo = null;

            if (request()->hasFile('logo')) {

                $logo = $this->mediaService->upload(
                    request()->file('logo'),
                    'brands'
                );
            }

            return Brand::create([

                'uuid' => (string) Str::uuid(),

                'name' => $data['name'],

                'slug' => Str::slug($data['name']),

                'description' => $data['description'] ?? null,

                'website' => $data['website'] ?? null,

                'logo' => $logo,

                'featured' => $data['featured'] ?? false,

                'status' => $data['status'] ?? true,

            ]);
        });
    }

    /**
     * Update Brand
     */
    public function update(
        Brand $brand,
        array $data
    ): Brand {

        return DB::transaction(function () use (
            $brand,
            $data
        ) {

            $logo = $brand->logo;

            if (request()->hasFile('logo')) {

                $logo = $this->mediaService->replace(

                    request()->file('logo'),

                    $brand->logo,

                    'brands'
                );
            }

            $brand->update([

                'name' => $data['name'],

                'slug' => Str::slug($data['name']),

                'description' => $data['description'] ?? null,

                'website' => $data['website'] ?? null,

                'logo' => $logo,

                'featured' => $data['featured'] ?? false,

                'status' => $data['status'] ?? true,

            ]);

            return $brand->fresh();
        });
    }

    /**
     * Delete Brand
     */
    public function delete(Brand $brand): void
    {
        DB::transaction(function () use ($brand) {

            if ($brand->logo) {

                $this->mediaService->delete(
                    $brand->logo
                );
            }

            $brand->delete();

        });
    }

    /**
     * Restore Brand
     */
    public function restore(string $uuid): Brand
    {
        $brand = Brand::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $brand->restore();

        return $brand;
    }

    /**
     * Force Delete Brand
     */
    public function forceDelete(string $uuid): void
    {
        $brand = Brand::withTrashed()
            ->where('uuid', $uuid)
            ->firstOrFail();

        if ($brand->logo) {

            $this->mediaService->delete(
                $brand->logo
            );
        }

        $brand->forceDelete();
    }

    /**
     * Toggle Featured
     */
    public function toggleFeatured(
        Brand $brand
    ): Brand {

        $brand->update([
            'featured' => ! $brand->featured,
        ]);

        return $brand->fresh();
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(
        Brand $brand
    ): Brand {

        $brand->update([
            'status' => ! $brand->status,
        ]);

        return $brand->fresh();
    }
}
