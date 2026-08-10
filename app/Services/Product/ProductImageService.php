<?php

namespace App\Services\Product;
use App\Services\Product\ProductImageService;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    /**
     * Upload multiple images.
     */
    public function upload(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {

            $uploadedImages = [];

            foreach ($data['images'] as $index => $file) {

                $path = $this->storeImage($product, $file);

                $image = ProductImage::create([

                    'product_id' => $product->id,

                    'image' => $path,

                    'alt_text' => $data['alt_text'][$index] ?? null,

                    'is_primary' => $product->images()->count() === 0 && $index === 0,

                    'sort_order' =>
($product->images()->max('sort_order') ?? 0)
+ $index
+ 1,

                ]);

                $uploadedImages[] = $image;
            }

            return collect($uploadedImages);

        });
    }

    /**
 * Backward compatible upload wrapper.
 */
public function uploadImages(
    Product $product,
    array $images,
    array $altTexts = []
) {
    return $this->upload($product, [
        'images' => $images,
        'alt_text' => $altTexts,
    ]);
}

    /**
     * Update image information.
     */
    public function update(ProductImage $image, array $data): ProductImage
    {
        return DB::transaction(function () use ($image, $data) {

            $image->update([

                'alt_text' => $data['alt_text'] ?? $image->alt_text,

            ]);

            if (($data['is_primary'] ?? false) === true) {

                $this->setPrimary($image);

            }

            return $image->fresh();

        });
    }

    /**
     * Delete image.
     */
    public function delete(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {

            Storage::disk('public')->delete($image->image);

            $product = $image->product;

            $wasPrimary = $image->is_primary;

            $image->delete();

            if ($wasPrimary) {

                $newPrimary = $product->images()

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

    /**
 * Delete multiple images.
 */
public function deleteImages(
    Product $product,
    array $imageIds
): void {

    $images = ProductImage::where(
        'product_id',
        $product->id
    )
    ->whereIn('id', $imageIds)
    ->get();

    foreach ($images as $image) {

        $this->delete($image);

    }

}

    /**
     * Reorder gallery.
     */
    public function reorder(Product $product, array $images): void
    {
        DB::transaction(function () use ($product, $images) {

            foreach ($images as $item) {

                ProductImage::where('product_id', $product->id)

                    ->where('uuid', $item['uuid'])

                    ->update([

                        'sort_order' => $item['sort_order'],

                    ]);

            }

        });
    }

    /**
     * Set image as primary.
     */
    public function setPrimary(ProductImage $image): void
    {
        DB::transaction(function () use ($image) {

            ProductImage::where(

                'product_id',

                $image->product_id

            )->update([

                'is_primary' => false,

            ]);

            $image->update([

                'is_primary' => true,

            ]);

        });
    }


    /**
 * Set primary image by id.
 */
public function setPrimaryImage(
    Product $product,
    int $imageId
): void {

    $image = ProductImage::where(
        'product_id',
        $product->id
    )
    ->findOrFail($imageId);

    $this->setPrimary($image);

}

    /**
     * Store uploaded image.
     */
    protected function storeImage(
        Product $product,
        UploadedFile $file
    ): string {

        return $file->store(

            'products/' . $product->uuid,

            'public'

        );

    }
}
