<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Concerns\ScopesToOwnSupplier;
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
    use ScopesToOwnSupplier;

    public function __construct(
        protected ProductVariantImageService $service
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Resolution helpers
    |--------------------------------------------------------------------------
    |
    | The route parameter must NOT be named {variant} — AppServiceProvider
    | registers a global Route::bind('variant', ...) that looks up
    | ProductVariant by uuid for ANY route parameter literally named
    | "variant", regardless of this controller's type-hints. Since these
    | routes deal with the numeric variant id, that global bind always
    | 404'd. Routes use {variant_id} instead to avoid it entirely, and
    | resolution happens explicitly here, scoped to the parent product.
    |
    */

    protected function resolveVariant(
        Product $product,
        int|string $variant_id
    ): ProductVariant {

        return ProductVariant::where('product_id', $product->id)
            ->where('id', $variant_id)
            ->firstOrFail();

    }

    protected function resolveImage(
        ProductVariant $variant,
        string $image
    ): ProductVariantImage {

        return ProductVariantImage::where('product_variant_id', $variant->id)
            ->where('uuid', $image)
            ->firstOrFail();

    }

    /**
     * Upload variant images.
     */
    public function upload(
        UploadProductVariantImagesRequest $request,
        Product $product,
        int|string $variant_id
    ): JsonResponse {
        try {
            $this->authorizeProductAccess($product);

            $variantModel = $this->resolveVariant(
                $product,
                $variant_id
            );

            $images = $this->service->upload(
                $variantModel,
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
        int|string $variant_id,
        string $image
    ): JsonResponse {
        try {
            $this->authorizeProductAccess($product);

            $variantModel = $this->resolveVariant(
                $product,
                $variant_id
            );

            $imageModel = $this->resolveImage(
                $variantModel,
                $image
            );

            $this->service->delete($imageModel);

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
        int|string $variant_id,
        string $image
    ): JsonResponse {
        try {
            $this->authorizeProductAccess($product);

            $variantModel = $this->resolveVariant(
                $product,
                $variant_id
            );

            $imageModel = $this->resolveImage(
                $variantModel,
                $image
            );

            $this->service->setPrimary($imageModel);

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
