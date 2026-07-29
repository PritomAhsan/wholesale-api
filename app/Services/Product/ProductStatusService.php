<?php

namespace App\Services\Product;

use App\Enums\ProductStatus;
use App\Enums\ProductStatusAction;
use App\Models\Product;
use App\Models\ProductStatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductStatusService
{
    protected array $allowedTransitions = [

        ProductStatus::APPROVED->value => [
            ProductStatus::PUBLISHED,
        ],

        ProductStatus::PUBLISHED->value => [
            ProductStatus::UNPUBLISHED,
            ProductStatus::ARCHIVED,
        ],

        ProductStatus::UNPUBLISHED->value => [
            ProductStatus::PUBLISHED,
            ProductStatus::ARCHIVED,
        ],

        ProductStatus::ARCHIVED->value => [
            ProductStatus::UNPUBLISHED,
        ],

    ];

    public function publish(Product $product, ?string $remarks=null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::PUBLISHED,
            ProductStatusAction::PUBLISHED,
            $remarks
        );
    }

    public function unpublish(Product $product, ?string $remarks=null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::UNPUBLISHED,
            ProductStatusAction::UNPUBLISHED,
            $remarks
        );
    }

    public function archive(Product $product, ?string $remarks=null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::ARCHIVED,
            ProductStatusAction::ARCHIVED,
            $remarks
        );
    }

    public function restore(Product $product, ?string $remarks=null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::UNPUBLISHED,
            ProductStatusAction::RESTORED,
            $remarks
        );
    }

    protected function changeStatus(
        Product $product,
        ProductStatus $newStatus,
        ProductStatusAction $action,
        ?string $remarks
    ): Product {

        return DB::transaction(function () use ($product,$newStatus,$action,$remarks){

            $product->refresh();

            $current = $product->status instanceof ProductStatus
                ? $product->status
                : ProductStatus::from($product->status);

            $allowed = $this->allowedTransitions[$current->value] ?? [];

            if (!in_array($newStatus, $allowed, true)) {
                throw new RuntimeException(
                    "Invalid status transition."
                );
            }

            $before = $current->value;

            $product->update([
                'status'=>$newStatus
            ]);

            ProductStatusHistory::create([

                'product_id'=>$product->id,

                'user_id'=>Auth::id(),

                'action'=>$action->value,

                'status_before'=>$before,

                'status_after'=>$newStatus->value,

                'remarks'=>$remarks,

                'performed_at'=>now(),

            ]);

            return $product->fresh();

        });
    }
}
