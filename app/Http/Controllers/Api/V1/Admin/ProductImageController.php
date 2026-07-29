<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\ProductImage\ReorderProductImagesRequest;
use App\Http\Requests\ProductImage\UpdateProductImageRequest;
use App\Http\Requests\ProductImage\UploadProductImagesRequest;
use App\Http\Resources\ProductImage\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Product\ProductImageService;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProductImageController extends ApiController
{
    public function __construct(
        protected ProductImageService $service
    ) {
    }

    /**
     * Ensure image belongs to product.
     */
    protected function ensureOwnership(
        Product $product,
        ProductImage $image
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
        UploadProductImagesRequest $request,
        Product $product
    ): JsonResponse {

        try {

            $images = $this->service->upload(

                $product,

                $request->validated()

            );

            return $this->success([

                'images' => ProductImageResource::collection($images)

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
    public function update(
        UpdateProductImageRequest $request,
        Product $product,
        ProductImage $image
    ): JsonResponse {

        try {

            $this->ensureOwnership($product, $image);

            $image = $this->service->update(

                $image,

                $request->validated()

            );

            return $this->success([

                'image' => new ProductImageResource($image)

            ], 'Image updated successfully.');

        } catch (Throwable $exception) {

            report($exception);

            return $this->error(

                $exception->getMessage(),

                500

            );

        }

    }

    /**
     * Delete image.
     */
    public function destroy(
        Product $product,
        ProductImage $image
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
        Product $product,
        ProductImage $image
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
    public function reorder(
        ReorderProductImagesRequest $request,
        Product $product
    ): JsonResponse {

        try {

            $this->service->reorder(

                $product,

                $request->validated()['images']

            );

            return $this->success(

                [],

                'Gallery reordered successfully.'

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
