<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Rfq\RfqResource;
use App\Models\Rfq;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RfqController extends ApiController
{
    public function index(Request $request)
    {
        $rfqs = Rfq::with('supplier', 'user')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($q) use ($request) {
                    $q->where('product_name', 'like', '%' . $request->search . '%')
                        ->orWhere('contact_name', 'like', '%' . $request->search . '%')
                        ->orWhere('contact_email', 'like', '%' . $request->search . '%');
                })
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'rfqs' => RfqResource::collection($rfqs),
            'pagination' => [
                'current_page' => $rfqs->currentPage(),
                'last_page' => $rfqs->lastPage(),
                'per_page' => $rfqs->perPage(),
                'total' => $rfqs->total(),
            ],
        ]);
    }

    public function show(string $uuid)
    {
        $rfq = Rfq::with('supplier', 'user', 'product', 'responder')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success([
            'rfq' => new RfqResource($rfq),
        ]);
    }

    /**
     * Admin responds to a buyer's RFQ — records the reply and moves
     * the request out of "pending".
     */
    public function respond(Request $request, string $uuid)
    {
        $rfq = Rfq::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([

            'status' => [
                'required',
                Rule::in(['quoted', 'accepted', 'rejected', 'closed']),
            ],

            'admin_response' => ['required', 'string', 'max:5000'],

        ]);

        $rfq->update([
            'status' => $data['status'],
            'admin_response' => $data['admin_response'],
            'responded_at' => now(),
            'responded_by' => $request->user()->id,
        ]);

        return $this->success([
            'rfq' => new RfqResource($rfq->fresh(['supplier', 'user', 'responder'])),
        ], 'Response sent.');
    }
}
