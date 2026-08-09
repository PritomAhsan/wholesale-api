<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductVariantImage\UploadProductVariantImagesRequest;
use App\Http\Resources\ProductVariantImage\ProductVariantImageResource;
use App\Models\Product;
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
     * Ensure variant belongs to product.
     */
    protected function ensureVariantOwnership(
        Product $product,
        ProductVariant $variant
    ): void {
        abort_unless(
            $variant->product_id === $product->id,
            404,
            'Variant not found.'
        );
    }

    /**
     * Ensure image belongs to variant.
     */
    protected function ensureImageOwnership(
        ProductVariant $variant,
        ProductVariantImage $image
    ): void {
        abort_unless(
            $image->product_variant_id === $variant->id,
            404,
            'Image not found.'
        );
    }

    /**
     * Upload variant images.
     */
    public function upload(
        UploadProductVariantImagesRequest $request,
        Product $product,
        ProductVariant $variant
    ): JsonResponse {
        try {
            $this->ensureVariantOwnership(
                $product,
                $variant
            );

            $images = $this->service->upload(
                $variant,
                $request->validated()
            );

            return $this->success(
                [
                    'images' =>
                        ProductVariantImageResource::collection(
                            $images
                        ),
                ],
                'Images uploaded successfully.',
                201
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
     * Delete variant image.
     */
    public function destroy(
        Product $product,
        ProductVariant $variant,
        ProductVariantImage $image
    ): JsonResponse {
        try {
            $this->ensureVariantOwnership(
                $product,
                $variant
            );

            $this->ensureImageOwnership(
                $variant,
                $image
            );

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
     * Set variant image as primary.
     */
    public function setPrimary(
        Product $product,
        ProductVariant $variant,
        ProductVariantImage $image
    ): JsonResponse {
        try {
            $this->ensureVariantOwnership(
                $product,
                $variant
            );

            $this->ensureImageOwnership(
                $variant,
                $image
            );

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
}
