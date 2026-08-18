<?php

namespace App\Services\Supplier;

use App\Models\Payout;
use App\Models\SellerOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Scaffolding only — no payout gateway (Stripe Connect) exists yet.
 * A request bundles a supplier's delivered, not-yet-paid-out seller
 * orders into a Payout record; an admin marks it paid manually once
 * the transfer is actually sent outside this system.
 */
class PayoutService
{
    public function requestFor(Supplier $supplier): Payout
    {
        return DB::transaction(function () use ($supplier) {

            $eligible = SellerOrder::where('supplier_id', $supplier->id)
                ->where('status', 'delivered')
                ->whereNull('payout_id')
                ->whereNotNull('payable_amount')
                ->lockForUpdate()
                ->get();

            if ($eligible->isEmpty()) {
                throw ValidationException::withMessages([
                    'payout' => ['There is nothing to pay out yet — no delivered orders are pending payout.'],
                ]);
            }

            $amount = $eligible->sum('payable_amount');

            $payout = Payout::create([

                'supplier_id' => $supplier->id,

                'amount' => $amount,

                'status' => 'requested',

                'requested_at' => now(),

            ]);

            SellerOrder::whereIn('id', $eligible->pluck('id'))
                ->update(['payout_id' => $payout->id]);

            return $payout->fresh();

        });
    }

    public function markPaid(Payout $payout, User $admin, ?string $note): Payout
    {
        $payout->update([

            'status' => 'paid',

            'paid_at' => now(),

            'paid_by' => $admin->id,

            'reference_note' => $note,

        ]);

        return $payout->fresh();
    }
}
