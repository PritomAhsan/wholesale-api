<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Supplier\RegisterSupplierRequest;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierController extends ApiController
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    public function register(
        RegisterSupplierRequest $request
    ) {
        try {

            $supplier = $this->supplierService->register(
                $request->user(),
                $request->validated()
            );

            return $this->success([
                'supplier' => new SupplierResource($supplier)
            ], 'Supplier registration submitted.', 201);

        } catch (ValidationException $e) {

            return $this->error(
                'Registration failed',
                $e->errors(),
                422
            );

        }
    }

    public function pending()
    {
        return $this->success([
            'suppliers' => SupplierResource::collection(
                Supplier::where('status', 'pending')
                    ->latest()
                    ->get()
            )
        ]);
    }

    public function approved()
    {
        return $this->success([
            'suppliers' => SupplierResource::collection(
                Supplier::where('status', 'approved')
                    ->latest()
                    ->get()
            )
        ]);
    }
}
