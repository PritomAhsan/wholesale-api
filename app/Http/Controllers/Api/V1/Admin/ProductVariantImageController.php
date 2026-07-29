<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductImage\ReorderProductImagesRequest;
use App\Http\Requests\ProductImage\UpdateProductImageRequest;
use App\Http\Requests\ProductVariantImage\UploadProductVariantImagesRequest;
use App\Http\Resources\ProductVariantImage\ProductVariantImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Services\Product\ProductVariantImageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProductVariantImageController extends ApiController
{
    public function __construct(
        protected ProductVariantImageService $service
    ) {
    }

    /**
     * Ensure image belongs to product.
     */
    protected function ensureOwnership(
        ProductVariant $product,
        ProductVariantImage $image
    ): void {

        abort_unless(

            $image->product_id === $product->id,

            404,

            'Image not found.'

        );

    }

    /**
     * Upload images.
     */
    public function upload(
        UploadProductVariantImagesRequest $request,
        ProductVariant $product
    ): JsonResponse {

        try {

            $images = $this->service->upload(

                $product,

                $request->validated()

            );

            return $this->success([

                'images' => ProductVariantImageResource::collection($images)

            ], 'Images uploaded successfully.', 201);

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /**
     * Update image.
     */


    /**
     * Delete image.
     */
    public function destroy(
        ProductVariant $product,
        ProductVariantImage $image
    ): JsonResponse {

        try {

            $this->ensureOwnership($product, $image);

            $this->service->delete($image);

            return $this->success(

                [],

                'Image deleted successfully.'

            );

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /**
     * Set primary image.
     */
    public function setPrimary(
        ProductVariant $product,
        ProductVariantImage $image
    ): JsonResponse {

        try {

            $this->ensureOwnership($product, $image);

            $this->service->setPrimary($image);

            return $this->success(

                [],

                'Primary image updated successfully.'

            );

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /**
     * Reorder gallery.
     */

}
