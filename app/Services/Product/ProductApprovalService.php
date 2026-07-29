<?php

namespace App\Services\Product;

use App\Enums\ProductApprovalAction;
use App\Enums\ProductApprovalDecision;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductApprovalService
{
    /**
     * Allowed status transitions.
     */
    protected array $allowedTransitions = [

        ProductStatus::DRAFT->value => [
            ProductStatus::PENDING,
        ],

        ProductStatus::PENDING->value => [
            ProductStatus::APPROVED,
            ProductStatus::REJECTED,
        ],

        ProductStatus::REJECTED->value => [
            ProductStatus::DRAFT,
            ProductStatus::PENDING,
        ],

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

        ],

    ];

        /**
     * Submit product for review.
     */
    public function submit(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::PENDING,
            ProductApprovalAction::SUBMITTED,
            ProductApprovalDecision::PENDING,
            $remarks
        );
    }

    /**
     * Re-submit a rejected product.
     */
    public function resubmit(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::PENDING,
            ProductApprovalAction::RESUBMITTED,
            ProductApprovalDecision::PENDING,
            $remarks
        );
    }

    /**
     * Approve a product.
     */
    public function approve(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::APPROVED,
            ProductApprovalAction::APPROVED,
            ProductApprovalDecision::APPROVED,
            $remarks
        );
    }

    /**
     * Reject a product.
     */
    public function reject(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::REJECTED,
            ProductApprovalAction::REJECTED,
            ProductApprovalDecision::REJECTED,
            $remarks
        );
    }

    /**
     * Publish product.
     */
    public function publish(Product $product): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::PUBLISHED,
            ProductApprovalAction::PUBLISHED,
            ProductApprovalDecision::APPROVED,
            null
        );
    }

    /**
     * Unpublish product.
     */
    public function unpublish(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::UNPUBLISHED,
            ProductApprovalAction::UNPUBLISHED,
            ProductApprovalDecision::APPROVED,
            $remarks
        );
    }

    /**
     * Archive product.
     */
    public function archive(Product $product, ?string $remarks = null): Product
    {
        return $this->changeStatus(
            $product,
            ProductStatus::ARCHIVED,
            ProductApprovalAction::ARCHIVED,
            ProductApprovalDecision::APPROVED,
            $remarks
        );
    }

        /**
     * Change product status.
     */
    protected function changeStatus(
        Product $product,
        ProductStatus $newStatus,
        ProductApprovalAction $action,
        ProductApprovalDecision $decision,
        ?string $remarks
    ): Product {

        return DB::transaction(function () use (
            $product,
            $newStatus,
            $action,
            $decision,
            $remarks
        ) {

            $product->refresh();

            $currentStatus = $product->status instanceof ProductStatus
                ? $product->status
                : ProductStatus::from($product->status);

            $this->validateTransition(
                $currentStatus,
                $newStatus
            );

            $before = $currentStatus->value;

            $product->update([
                'status' => $newStatus,
            ]);

            $this->recordApproval(
                $product,
                $action,
                $decision,
                $before,
                $newStatus->value,
                $remarks
            );

            return $product->fresh();

        });

    }

        /**
     * Validate status transition.
     */
    protected function validateTransition(
        ProductStatus $current,
        ProductStatus $next
    ): void {

        $allowed = $this->allowedTransitions[
            $current->value
        ] ?? [];

        foreach ($allowed as $status) {

            if ($status === $next) {
                return;
            }

        }

        throw new RuntimeException(

            sprintf(
                'Invalid status transition: %s → %s',
                $current->value,
                $next->value
            )

        );

    }

        /**
     * Store approval history.
     */
    protected function recordApproval(
        Product $product,
        ProductApprovalAction $action,
        ProductApprovalDecision $decision,
        string $before,
        string $after,
        ?string $remarks
    ): ProductApproval {

        return ProductApproval::create([

            'product_id' => $product->id,

            'reviewer_id' => Auth::id(),

            'action' => $action->value,

            'decision' => $decision->value,

            'status_before' => $before,

            'status_after' => $after,

            'remarks' => $remarks,

            'reviewed_at' => now(),

        ]);

    }
}
