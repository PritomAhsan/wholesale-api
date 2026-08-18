<?php

namespace App\Http\Controllers\Api\V1\Rfq;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Rfq\StoreRfqRequest;
use App\Http\Resources\Rfq\RfqResource;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\Supplier;

class RfqController extends ApiController
{
    public function index()
    {
        $rfqs = Rfq::where('user_id', auth('sanctum')->id())
            ->with('supplier')
            ->latest()
            ->paginate(20);

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
        $rfq = Rfq::where('user_id', auth('sanctum')->id())
            ->where('uuid', $uuid)
            ->with('supplier')
            ->firstOrFail();

        return $this->success([
            'rfq' => new RfqResource($rfq),
        ]);
    }

    public function store(StoreRfqRequest $request)
    {
        $data = $request->validated();

        $supplier = isset($data['supplier_uuid'])
            ? Supplier::where('uuid', $data['supplier_uuid'])->first()
            : null;

        $product = isset($data['product_uuid'])
            ? Product::where('uuid', $data['product_uuid'])->first()
            : null;

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {

            $attachmentPath = $request->file('attachment')->store(
                'rfq-attachments',
                'public'
            );

        }

        $rfq = Rfq::create([

            'user_id' => auth('sanctum')->user()?->id,

            'supplier_id' => $supplier?->id,

            'product_id' => $product?->id,

            'product_name' => $data['product_name'],

            'preferred_supplier_name' => $data['preferred_supplier_name'] ?? null,

            'quantity' => $data['quantity'],

            'unit' => $data['unit'] ?? 'Pieces',

            'budget' => $data['budget'] ?? null,

            'destination_country' => $data['destination_country'],

            'required_delivery_date' => $data['required_delivery_date'] ?? null,

            'message' => $data['message'],

            'attachment_path' => $attachmentPath,

            'contact_name' => $data['contact_name'],

            'contact_email' => $data['contact_email'],

            'contact_phone' => $data['contact_phone'] ?? null,

            'status' => 'pending',

        ]);

        return $this->success([
            'rfq' => new RfqResource($rfq),
        ], 'Your request for quotation has been submitted.', 201);
    }
}
