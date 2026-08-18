<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Deal\StoreDealRequest;
use App\Http\Resources\Deal\AdminDealResource;
use App\Http\Resources\Deal\DealAlertSubscriptionResource;
use App\Models\Deal;
use App\Models\DealAlertSubscription;
use Illuminate\Http\Request;

class DealController extends ApiController
{
    public function alertSubscriptions(Request $request)
    {
        $subscriptions = DealAlertSubscription::query()
            ->latest('subscribed_at')
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'subscriptions' => DealAlertSubscriptionResource::collection($subscriptions),
            'pagination' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'per_page' => $subscriptions->perPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $deals = Deal::query()
            ->with('product')
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', $request->type)
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'deals' => AdminDealResource::collection($deals),
            'pagination' => [
                'current_page' => $deals->currentPage(),
                'last_page' => $deals->lastPage(),
                'per_page' => $deals->perPage(),
                'total' => $deals->total(),
            ],
        ]);
    }

    public function show(Deal $deal)
    {
        $deal->load('product');

        return $this->success([
            'deal' => new AdminDealResource($deal),
        ]);
    }

    public function store(StoreDealRequest $request)
    {
        $deal = Deal::create($request->validated() + ['status' => $request->get('status', 'active')]);

        return $this->success([
            'deal' => new AdminDealResource($deal->load('product')),
        ], 'Deal created.', 201);
    }

    public function update(StoreDealRequest $request, Deal $deal)
    {
        $deal->update($request->validated());

        return $this->success([
            'deal' => new AdminDealResource($deal->fresh()->load('product')),
        ], 'Deal updated.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();

        return $this->success(null, 'Deal deleted.');
    }
}
