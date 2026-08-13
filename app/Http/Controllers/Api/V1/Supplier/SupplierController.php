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

    /**
     * All suppliers, with optional status/search filters (admin).
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::query()
            ->withCount('products')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'company_name',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'suppliers' => SupplierResource::collection($suppliers),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
        ]);
    }

    /**
     * The authenticated user's own supplier application, if any.
     */
    public function me(Request $request)
    {
        $supplier = $request->user()->supplier()->first();

        return $this->success([
            'supplier' => $supplier ? new SupplierResource($supplier) : null,
        ]);
    }
}
