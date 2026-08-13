<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Order\SellerOrderResource;
use App\Models\SellerOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierOrderController extends ApiController
{
    /**
     * Orders containing this supplier's products — a supplier only
     * ever sees their own slice of an order, never the buyer's full
     * order or other sellers' items within it.
     */
    public function index(Request $request)
    {
        $supplier = $request->user()->supplier;

        if (! $supplier) {
            return $this->error('No supplier account found.', null, 403);
        }

        $sellerOrders = SellerOrder::with('supplier', 'order')
            ->where('supplier_id', $supplier->id)
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'orders' => SellerOrderResource::collection($sellerOrders),
            'pagination' => [
                'current_page' => $sellerOrders->currentPage(),
                'last_page' => $sellerOrders->lastPage(),
                'per_page' => $sellerOrders->perPage(),
                'total' => $sellerOrders->total(),
            ],
        ]);
    }

    public function show(Request $request, string $uuid)
    {
        $supplier = $request->user()->supplier;

        if (! $supplier) {
            return $this->error('No supplier account found.', null, 403);
        }

        $sellerOrder = SellerOrder::with('supplier', 'items.product', 'order')
            ->where('uuid', $uuid)
            ->where('supplier_id', $supplier->id)
            ->firstOrFail();

        return $this->success([
            'order' => new SellerOrderResource($sellerOrder),
        ]);
    }

    /**
     * A supplier fulfilling their own portion of an order — the same
     * action available to admin, scoped to orders they actually own.
     */
    public function updateStatus(Request $request, string $uuid)
    {
        $supplier = $request->user()->supplier;

        if (! $supplier) {
            return $this->error('No supplier account found.', null, 403);
        }

        $sellerOrder = SellerOrder::where('uuid', $uuid)
            ->where('supplier_id', $supplier->id)
            ->firstOrFail();

        $data = $request->validate([

            'status' => [
                'required',
                Rule::in(['processing', 'shipped', 'delivered']),
            ],

            'tracking_number' => ['nullable', 'string', 'max:255'],

            'shipping_carrier' => ['nullable', 'string', 'max:255'],

        ]);

        if ($data['status'] === 'shipped' && $sellerOrder->status !== 'shipped') {
            $data['shipped_at'] = now();
        }

        if ($data['status'] === 'delivered' && $sellerOrder->status !== 'delivered') {
            $data['delivered_at'] = now();
        }

        $sellerOrder->update($data);

        return $this->success([
            'order' => new SellerOrderResource(
                $sellerOrder->fresh(['supplier', 'items.product'])
            ),
        ], 'Order status updated.');
    }
}
