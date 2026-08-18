<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared ownership scoping for controllers whose routes are reachable
 * by both admin staff and Supplier-role users. A user with ONLY the
 * Supplier role (not also Admin/Super Admin) must never see, modify
 * or act on another supplier's products.
 */
trait ScopesToOwnSupplier
{
    /**
     * True if the current user is a supplier-only account (no admin
     * roles), meaning access must be scoped to their own data.
     */
    protected function isSupplierOnly(): bool
    {
        $user = request()->user();

        return $user
            && $user->hasRole('Supplier')
            && ! $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * The authenticated supplier's own supplier_id, or abort with 403
     * if this user has the Supplier role but no approved supplier
     * record (shouldn't normally happen, but fail closed rather than
     * exposing/scoping to nothing).
     */
    protected function ownSupplierId(): int
    {
        $supplier = request()->user()->supplier;

        abort_if(
            ! $supplier,
            403,
            'No supplier account is associated with this user.'
        );

        return $supplier->id;
    }

    /**
     * Restrict a product-scoped query to the current supplier's own
     * products when the user is supplier-only. No-op for admin staff.
     */
    protected function scopeToOwnSupplier(Builder $query): Builder
    {
        if (! $this->isSupplierOnly()) {
            return $query;
        }

        return $query->where('supplier_id', $this->ownSupplierId());
    }

    /**
     * Abort with 403 if this is a supplier-only user trying to touch
     * a product that isn't theirs. No-op for admin staff.
     */
    protected function authorizeProductAccess(Product $product): void
    {
        if (! $this->isSupplierOnly()) {
            return;
        }

        abort_unless(
            $product->supplier_id === $this->ownSupplierId(),
            403,
            'You do not have access to this product.'
        );
    }

    /**
     * Force supplier_id to the current user's own supplier on
     * create/update, regardless of what was submitted — a supplier
     * must never be able to create or reassign a product to another
     * supplier's name.
     */
    protected function enforceOwnSupplierId(array $data): array
    {
        if ($this->isSupplierOnly()) {
            $data['supplier_id'] = $this->ownSupplierId();
        }

        return $data;
    }

    /**
     * Strip fields a supplier-only user must never set directly —
     * these represent moderation/marketing decisions that belong to
     * the approve/reject/publish/archive/featured endpoints (admin
     * only), not the general create/update form. Without this, a
     * supplier could submit status=published on update() and bypass
     * the approval workflow entirely.
     */
    protected function stripSupplierRestrictedFields(array $data, bool $isCreate): array
    {
        if (! $this->isSupplierOnly()) {
            return $data;
        }

        unset($data['featured']);

        if ($isCreate) {
            // A supplier's new product always starts as a draft —
            // review/publish happens through the approval workflow.
            $data['status'] = 'draft';
        } else {
            // Status changes on an existing product must go through
            // the approval endpoints, never the general update form.
            unset($data['status']);
        }

        return $data;
    }
}
