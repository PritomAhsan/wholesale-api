<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Supplier\MarkPayoutPaidRequest;
use App\Http\Resources\Supplier\PayoutResource;
use App\Models\Payout;
use App\Services\Supplier\PayoutService;
use Illuminate\Http\Request;

class PayoutController extends ApiController
{
    public function __construct(
        protected PayoutService $service
    ) {
    }

    public function index(Request $request)
    {
        $payouts = Payout::with('supplier')
            ->withCount('sellerOrders')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest('requested_at')
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'payouts' => PayoutResource::collection($payouts),
            'pagination' => [
                'current_page' => $payouts->currentPage(),
                'last_page' => $payouts->lastPage(),
                'per_page' => $payouts->perPage(),
                'total' => $payouts->total(),
            ],
        ]);
    }

    public function show(Payout $payout)
    {
        $payout->load('supplier', 'sellerOrders.order')->loadCount('sellerOrders');

        return $this->success([
            'payout' => new PayoutResource($payout),
        ]);
    }

    public function markPaid(MarkPayoutPaidRequest $request, Payout $payout)
    {
        if ($payout->status === 'paid') {
            return $this->error('This payout has already been marked paid.', null, 422);
        }

        $payout = $this->service->markPaid(
            $payout,
            $request->user(),
            $request->validated('reference_note')
        );

        return $this->success([
            'payout' => new PayoutResource($payout->load('supplier')),
        ], 'Payout marked as paid.');
    }
}
