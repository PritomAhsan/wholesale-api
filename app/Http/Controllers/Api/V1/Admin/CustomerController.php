<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function index(Request $request)
    {
        $customers = User::role('Customer')
            ->withCount('orders')
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($q) use ($request) {
                    $q->where('first_name', 'like', '%' . $request->search . '%')
                        ->orWhere('last_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                })
            )
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'customers' => CustomerResource::collection($customers),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }
}
