<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Supplier\PayoutResource;
use App\Models\SellerOrder;
use App\Services\Supplier\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayoutController extends ApiController
{
    public function __construct(
        protected PayoutService $service
    ) {
    }

    public function index(Request $request)
    {
        $supplier = $request->user()->supplier;

        abort_if(! $supplier, 403, 'No supplier account found.');

        $payouts = $supplier->payouts()
            ->withCount('sellerOrders')
            ->latest('requested_at')
            ->paginate($request->integer('per_page', 20));

        $pendingAmount = SellerOrder::where('supplier_id', $supplier->id)
            ->where('status', 'delivered')
            ->whereNull('payout_id')
            ->whereNotNull('payable_amount')
            ->sum('payable_amount');

        return $this->success([
            'payouts' => PayoutResource::collection($payouts),
            'pagination' => [
                'current_page' => $payouts->currentPage(),
                'last_page' => $payouts->lastPage(),
                'per_page' => $payouts->perPage(),
                'total' => $payouts->total(),
            ],
            'pending_amount' => (float) $pendingAmount,
        ]);
    }

    public function store(Request $request)
    {
        $supplier = $request->user()->supplier;

        abort_if(! $supplier, 403, 'No supplier account found.');

        try {

            $payout = $this->service->requestFor($supplier);

        } catch (ValidationException $e) {

            return $this->error($e->getMessage(), $e->errors(), 422);

        }

        return $this->success([
            'payout' => new PayoutResource($payout),
        ], 'Payout requested.', 201);
    }
}
