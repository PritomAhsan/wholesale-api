<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\DisputeImage;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * No live payment gateway exists yet, so resolving a dispute cannot
 * process a real refund. What it does instead is real and load-
 * bearing: a refund resolution reduces the seller order's
 * payable_amount — the same column PayoutService already sums to
 * decide what a supplier can request — so a refunded order can't
 * still be paid out in full. If it's already been paid out
 * (payout_id set), the amount can no longer be adjusted here and the
 * resolution is recorded with a note flagging manual reconciliation.
 */
class DisputeService
{
    public function open(User $buyer, SellerOrder $sellerOrder, array $data): Dispute
    {
        return DB::transaction(function () use ($buyer, $sellerOrder, $data) {

            if ($sellerOrder->status !== 'delivered') {
                throw ValidationException::withMessages([
                    'dispute' => ['Only delivered orders can be disputed.'],
                ]);
            }

            if ($sellerOrder->disputes()->open()->exists()) {
                throw ValidationException::withMessages([
                    'dispute' => ['There is already an open dispute for this order.'],
                ]);
            }

            $dispute = Dispute::create([

                'seller_order_id' => $sellerOrder->id,

                'user_id' => $buyer->id,

                'reason' => $data['reason'],

                'description' => $data['description'],

                'status' => 'open',

            ]);

            foreach ($data['images'] ?? [] as $file) {
                $this->attachImage($dispute, $buyer, $file);
            }

            return $dispute->fresh('images');

        });
    }

    public function addEvidence(Dispute $dispute, User $uploader, array $files): Dispute
    {
        foreach ($files as $file) {
            $this->attachImage($dispute, $uploader, $file);
        }

        return $dispute->fresh('images');
    }

    public function resolve(Dispute $dispute, User $admin, array $data): Dispute
    {
        return DB::transaction(function () use ($dispute, $admin, $data) {

            $sellerOrder = $dispute->sellerOrder;

            $resolutionAmount = match ($data['resolution']) {
                'refund_full' => (float) $sellerOrder->payable_amount,
                'refund_partial' => (float) $data['resolution_amount'],
                default => null,
            };

            $note = $data['resolution_note'] ?? null;

            if (in_array($data['resolution'], ['refund_full', 'refund_partial'], true)) {

                if ($sellerOrder->payout_id !== null) {

                    $note = trim(
                        ($note ? $note . ' ' : '') .
                        '[This seller order was already paid out — the payable amount could not be adjusted automatically. Reconcile the refund with the supplier manually.]'
                    );

                } else {

                    $sellerOrder->update([
                        'payable_amount' => max(
                            0,
                            (float) $sellerOrder->payable_amount - $resolutionAmount
                        ),
                    ]);

                }

            }

            $dispute->update([

                'status' => 'resolved',

                'resolution' => $data['resolution'],

                'resolution_amount' => $resolutionAmount,

                'resolution_note' => $note,

                'resolved_at' => now(),

                'resolved_by' => $admin->id,

            ]);

            return $dispute->fresh(['images', 'resolver']);

        });
    }

    protected function attachImage(Dispute $dispute, User $uploader, UploadedFile $file): DisputeImage
    {
        $path = $file->store('disputes/' . $dispute->uuid, 'public');

        return DisputeImage::create([

            'dispute_id' => $dispute->id,

            'uploaded_by' => $uploader->id,

            'image' => $path,

        ]);
    }
}
