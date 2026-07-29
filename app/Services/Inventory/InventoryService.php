<?php

namespace App\Services\Inventory;

use App\Enums\InventoryMovementType;
use App\Enums\InventoryTransactionType;
use App\Models\InventoryTransaction;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Add stock.
     */
    public function increase(
        ProductVariant $variant,
        int $quantity,
        InventoryTransactionType $type,
        ?Model $reference = null,
        ?string $remarks = null
    ): ProductVariant {

        if ($quantity <= 0) {

            throw new RuntimeException(
                'Quantity must be greater than zero.'
            );

        }

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $reference,
            $remarks
        ) {

            $variant->refresh();

            $before = $variant->stock_quantity;

            $after = $before + $quantity;

            $variant->update([
                'stock_quantity' => $after,
            ]);

            $this->createTransaction(
                $variant,
                $type,
                InventoryMovementType::IN,
                $quantity,
                $before,
                $after,
                $reference,
                $remarks
            );

            return $variant->fresh();

        });

    }

    /**
     * Remove stock.
     */
    public function decrease(
        ProductVariant $variant,
        int $quantity,
        InventoryTransactionType $type,
        ?Model $reference = null,
        ?string $remarks = null
    ): ProductVariant {

        if ($quantity <= 0) {

            throw new RuntimeException(
                'Quantity must be greater than zero.'
            );

        }

        return DB::transaction(function () use (
            $variant,
            $quantity,
            $type,
            $reference,
            $remarks
        ) {

            $variant->refresh();

            if ($variant->stock_quantity < $quantity) {

                throw new RuntimeException(
                    'Insufficient inventory.'
                );

            }

            $before = $variant->stock_quantity;

            $after = $before - $quantity;

            $variant->update([
                'stock_quantity' => $after,
            ]);

            $this->createTransaction(
                $variant,
                $type,
                InventoryMovementType::OUT,
                $quantity,
                $before,
                $after,
                $reference,
                $remarks
            );

            return $variant->fresh();

        });

    }

    /**
     * Set exact stock quantity.
     */
    public function adjust(
        ProductVariant $variant,
        int $newQuantity,
        ?string $remarks = null
    ): ProductVariant {

        if ($newQuantity < 0) {

            throw new RuntimeException(
                'Stock cannot be negative.'
            );

        }

        return DB::transaction(function () use (
            $variant,
            $newQuantity,
            $remarks
        ) {

            $variant->refresh();

            $before = $variant->stock_quantity;

            $variant->update([
                'stock_quantity' => $newQuantity,
            ]);

            $movement = $newQuantity > $before
                ? InventoryMovementType::IN
                : (
                    $newQuantity < $before
                        ? InventoryMovementType::OUT
                        : InventoryMovementType::NONE
                );

            $this->createTransaction(
                $variant,
                InventoryTransactionType::ADJUSTMENT,
                $movement,
                abs($newQuantity - $before),
                $before,
                $newQuantity,
                null,
                $remarks
            );

            return $variant->fresh();

        });

    }

    /**
     * Create inventory transaction.
     */
    protected function createTransaction(
        ProductVariant $variant,
        InventoryTransactionType $transactionType,
        InventoryMovementType $movementType,
        int $quantity,
        int $before,
        int $after,
        ?Model $reference,
        ?string $remarks
    ): InventoryTransaction {

        return InventoryTransaction::create([

            'product_variant_id' => $variant->id,

            'transaction_type' => $transactionType->value,

            'movement_type' => $movementType->value,

            'quantity' => $quantity,

            'stock_before' => $before,

            'stock_after' => $after,

            'reference_type' => $reference?->getMorphClass(),

            'reference_id' => $reference?->getKey(),

            'remarks' => $remarks,

            'created_by' => Auth::id(),

        ]);

    }
}
