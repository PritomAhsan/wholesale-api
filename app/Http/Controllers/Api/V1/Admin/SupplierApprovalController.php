<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupplierApprovalController extends ApiController
{
    public function __construct(
        protected SupplierService $supplierService
    ) {}

    public function approve(
        Request $request,
        Supplier $supplier
    ) {
        try {

            $supplier = $this->supplierService->approve(
                $supplier,
                $request->user()
            );

            return $this->success([
                'supplier' => new SupplierResource($supplier)
            ], 'Supplier approved successfully.');

        } catch (ValidationException $e) {

            return $this->error(
                'Approval failed',
                $e->errors(),
                422
            );

        }
    }

    public function reject(
        Request $request,
        Supplier $supplier
    ) {
        try {

            $supplier = $this->supplierService->reject(
                $supplier,
                $request->user()
            );

            return $this->success([
                'supplier' => new SupplierResource($supplier)
            ], 'Supplier rejected.');

        } catch (ValidationException $e) {

            return $this->error(
                'Rejection failed',
                $e->errors(),
                422
            );

        }
    }
}
