<?php

namespace Database\Seeders;

use App\Models\Payout;
use App\Models\SellerOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PayoutSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('Super Admin')->first();
        $suppliers = Supplier::where('status', 'approved')->get();

        foreach ($suppliers as $index => $supplier) {
            $eligible = SellerOrder::where('supplier_id', $supplier->id)
                ->where('status', 'delivered')
                ->whereNull('payout_id')
                ->whereNotNull('payable_amount')
                ->get();

            if ($eligible->isEmpty()) {
                continue;
            }

            // Every other supplier gets an already-paid payout covering
            // their earlier delivered orders; the rest are left as
            // outstanding payable balance to request.
            if ($index % 2 !== 0) {
                continue;
            }

            $toPayOut = $eligible->take((int) ceil($eligible->count() / 2));
            if ($toPayOut->isEmpty()) {
                continue;
            }

            $amount = $toPayOut->sum('payable_amount');

            $payout = Payout::create([
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'status' => 'paid',
                'requested_at' => now()->subDays(rand(20, 60)),
                'paid_at' => now()->subDays(rand(1, 19)),
                'paid_by' => $admin?->id,
                'reference_note' => 'Bank transfer confirmed manually.',
            ]);

            SellerOrder::whereIn('id', $toPayOut->pluck('id'))
                ->update(['payout_id' => $payout->id]);
        }
    }
}
