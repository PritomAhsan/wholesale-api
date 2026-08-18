<?php

namespace App\Http\Controllers\Api\V1\Dispute;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Dispute\AddDisputeEvidenceRequest;
use App\Http\Requests\Dispute\StoreDisputeRequest;
use App\Http\Resources\Dispute\DisputeResource;
use App\Models\Dispute;
use App\Models\SellerOrder;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends ApiController
{
    public function __construct(
        protected DisputeService $service
    ) {
    }

    public function index(Request $request)
    {
        $disputes = Dispute::where('user_id', $request->user()->id)
            ->with(['sellerOrder.supplier', 'images.uploader'])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'disputes' => DisputeResource::collection($disputes),
            'pagination' => [
                'current_page' => $disputes->currentPage(),
                'last_page' => $disputes->lastPage(),
                'per_page' => $disputes->perPage(),
                'total' => $disputes->total(),
            ],
        ]);
    }

    public function show(Request $request, Dispute $dispute)
    {
        abort_unless($dispute->user_id === $request->user()->id, 403);

        $dispute->load(['sellerOrder.supplier', 'images.uploader']);

        return $this->success([
            'dispute' => new DisputeResource($dispute),
        ]);
    }

    public function store(StoreDisputeRequest $request, string $sellerOrderUuid)
    {
        $sellerOrder = SellerOrder::where('uuid', $sellerOrderUuid)
            ->whereHas('order', fn ($q) => $q->where('user_id', $request->user()->id))
            ->firstOrFail();

        try {

            $dispute = $this->service->open(
                $request->user(),
                $sellerOrder,
                $request->validated()
            );

        } catch (\Illuminate\Validation\ValidationException $e) {

            return $this->error($e->getMessage(), $e->errors(), 422);

        }

        return $this->success([
            'dispute' => new DisputeResource($dispute->load(['sellerOrder.supplier', 'images.uploader'])),
        ], 'Dispute opened.', 201);
    }

    public function addEvidence(AddDisputeEvidenceRequest $request, Dispute $dispute)
    {
        abort_unless($dispute->user_id === $request->user()->id, 403);

        if ($dispute->status !== 'open') {
            return $this->error('This dispute is no longer open.', null, 422);
        }

        $dispute = $this->service->addEvidence(
            $dispute,
            $request->user(),
            $request->file('images')
        );

        return $this->success([
            'dispute' => new DisputeResource($dispute->load(['sellerOrder.supplier', 'images.uploader'])),
        ], 'Evidence added.');
    }
}
