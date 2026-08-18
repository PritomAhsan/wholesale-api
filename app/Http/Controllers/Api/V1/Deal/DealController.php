<?php

namespace App\Http\Controllers\Api\V1\Deal;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Deal\DealResource;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends ApiController
{
    public function index(Request $request)
    {
        $deals = Deal::query()
            ->active()
            ->with(['product', 'product.primaryImage'])
            ->whereHas('product', fn ($q) => $q->where('status', 'published'))
            ->when(
                $request->filled('type'),
                fn ($query) => $query->where('type', $request->type)
            )
            ->orderBy(
                $request->get('type') === 'bulk' ? 'min_quantity' : 'ends_at'
            )
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'deals' => DealResource::collection($deals),
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
        $deal->load(['product', 'product.primaryImage']);

        return $this->success([
            'deal' => new DealResource($deal),
        ]);
    }
}
