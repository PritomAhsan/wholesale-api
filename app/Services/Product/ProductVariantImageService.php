<?php

namespace App\Services\Product;

use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductVariantImageService
{
    public function upload(ProductVariant $variant, array $data)
    {
        return DB::transaction(function () use ($variant, $data) {

            $uploaded = [];

            foreach ($data['images'] as $index => $file) {

                $path = $this->storeImage($variant, $file);

                $image = ProductVariantImage::create([

                    'product_variant_id' => $variant->id,

                    'image' => $path,

                    'is_primary' => $variant->images()->count() === 0 && $index === 0,

                    'sort_order' => $variant->images()->max('sort_order') + $index + 1,

                ]);

                $uploaded[] = $image;
            }

            return collect($uploaded);

        });
    }

    public function delete(ProductVariantImage $image): void
    {
        DB::transaction(function () use ($image) {

            Storage::disk('public')->delete($image->image);

            $variant = $image->variant;

            $wasPrimary = $image->is_primary;

            $image->delete();

            if ($wasPrimary) {

                $newPrimary = $variant->images()

                    ->orderBy('sort_order')

                    ->first();

                if ($newPrimary) {

                    $newPrimary->update([

                        'is_primary' => true,

                    ]);

                }
            }

        });
    }

    public function setPrimary(ProductVariantImage $image): void
    {
        DB::transaction(function () use ($image) {

            ProductVariantImage::where(

                'product_variant_id',

                $image->product_variant_id

            )->update([

                'is_primary' => false,

            ]);

            $image->update([

                'is_primary' => true,

            ]);

        });
    }

    protected function storeImage(
        ProductVariant $variant,
        UploadedFile $file
    ): string {

        return $file->store(

            'products/' . $variant->product->uuid . '/variants/' . $variant->uuid,

            'public'

        );
    }
}
