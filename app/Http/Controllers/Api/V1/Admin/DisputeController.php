<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Dispute\AddDisputeEvidenceRequest;
use App\Http\Requests\Dispute\ResolveDisputeRequest;
use App\Http\Resources\Dispute\DisputeResource;
use App\Models\Dispute;
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
        $disputes = Dispute::with(['sellerOrder.supplier', 'user'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
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

    public function show(Dispute $dispute)
    {
        $dispute->load(['sellerOrder.supplier', 'user', 'resolver', 'images.uploader']);

        return $this->success([
            'dispute' => new DisputeResource($dispute),
        ]);
    }

    public function resolve(ResolveDisputeRequest $request, Dispute $dispute)
    {
        if ($dispute->status !== 'open') {
            return $this->error('This dispute has already been resolved.', null, 422);
        }

        $dispute = $this->service->resolve(
            $dispute,
            $request->user(),
            $request->validated()
        );

        return $this->success([
            'dispute' => new DisputeResource(
                $dispute->load(['sellerOrder.supplier', 'user', 'resolver', 'images.uploader'])
            ),
        ], 'Dispute resolved.');
    }

    public function addEvidence(AddDisputeEvidenceRequest $request, Dispute $dispute)
    {
        if ($dispute->status !== 'open') {
            return $this->error('This dispute is no longer open.', null, 422);
        }

        $dispute = $this->service->addEvidence(
            $dispute,
            $request->user(),
            $request->file('images')
        );

        return $this->success([
            'dispute' => new DisputeResource($dispute->load(['sellerOrder.supplier', 'user', 'images.uploader'])),
        ], 'Evidence added.');
    }
}
