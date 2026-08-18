<?php

namespace Database\Seeders;

use App\Models\Dispute;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisputeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role('Super Admin')->first();

        $deliveredOrders = SellerOrder::where('status', 'delivered')
            ->with('order.user')
            ->inRandomOrder()
            ->take(5)
            ->get();

        if ($deliveredOrders->isEmpty()) {
            return;
        }

        $scenarios = [
            [
                'reason' => 'damaged',
                'description' => 'Around 15% of units in this shipment arrived with cracked casings. Photos attached to the original support ticket.',
                'status' => 'open',
            ],
            [
                'reason' => 'quantity_mismatch',
                'description' => 'Invoice says 500 units but only 460 were received in the carton count.',
                'status' => 'open',
            ],
            [
                'reason' => 'not_received',
                'description' => 'Tracking shows delivered but nothing arrived at our warehouse dock.',
                'status' => 'resolved',
                'resolution' => 'refund_full',
            ],
            [
                'reason' => 'wrong_item',
                'description' => 'Received a different color variant than what was ordered.',
                'status' => 'resolved',
                'resolution' => 'refund_partial',
            ],
            [
                'reason' => 'late_shipment',
                'description' => 'Order shipped 9 days after the confirmed dispatch window, causing a delay downstream.',
                'status' => 'resolved',
                'resolution' => 'no_action',
            ],
        ];

        foreach ($deliveredOrders as $i => $sellerOrder) {
            $scenario = $scenarios[$i % count($scenarios)];
            $user = $sellerOrder->order?->user;
            if (! $user) {
                continue;
            }

            $dispute = Dispute::updateOrCreate(
                ['seller_order_id' => $sellerOrder->id],
                [
                    'user_id' => $user->id,
                    'reason' => $scenario['reason'],
                    'description' => $scenario['description'],
                    'status' => $scenario['status'],
                ]
            );

            if ($scenario['status'] === 'resolved') {
                $resolutionAmount = match ($scenario['resolution']) {
                    'refund_full' => (float) $sellerOrder->payable_amount,
                    'refund_partial' => round((float) $sellerOrder->payable_amount * 0.4, 2),
                    default => null,
                };

                $note = 'Resolved after reviewing evidence with the seller.';

                if (in_array($scenario['resolution'], ['refund_full', 'refund_partial'], true)) {
                    if ($sellerOrder->payout_id !== null) {
                        $note .= ' [This seller order was already paid out — the payable amount could not be adjusted automatically. Reconcile the refund with the supplier manually.]';
                    } else {
                        $sellerOrder->update([
                            'payable_amount' => max(0, (float) $sellerOrder->payable_amount - $resolutionAmount),
                        ]);
                    }
                }

                $dispute->update([
                    'resolution' => $scenario['resolution'],
                    'resolution_amount' => $resolutionAmount,
                    'resolution_note' => $note,
                    'resolved_at' => now()->subDays(rand(1, 15)),
                    'resolved_by' => $admin?->id,
                ]);
            }
        }
    }
}
