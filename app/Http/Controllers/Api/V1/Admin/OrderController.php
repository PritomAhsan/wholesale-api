<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Order\OrderResource;
use App\Models\Order;
use App\Models\SellerOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends ApiController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * All orders on the platform, with optional status/search filters.
     */
    public function index(Request $request)
    {
        $orders = Order::with('user', 'sellerOrders.supplier')
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->status)
            )
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(
                    'order_number',
                    'like',
                    '%' . $request->search . '%'
                )
            )
            ->latest('placed_at')
            ->paginate(
                $request->integer('per_page', 20)
            );

        return $this->success([
            'orders' => OrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * A single order, any buyer — admin is not ownership-scoped.
     */
    public function show(string $uuid)
    {
        $order = Order::with(
            'user',
            'sellerOrders.supplier',
            'sellerOrders.items.product'
        )
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Update a seller order's fulfillment status and tracking info.
     */
    public function updateSellerOrderStatus(Request $request, string $uuid)
    {
        $sellerOrder = SellerOrder::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([

            'status' => [
                'required',
                Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
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

        $order = $sellerOrder->order()->with(
            'user',
            'sellerOrders.supplier',
            'sellerOrders.items.product'
        )->first();

        return $this->success([
            'order' => new OrderResource($order),
        ], 'Order status updated.');
    }

    /**
     * Cancel an order on behalf of any buyer.
     */
    public function cancel(Request $request, string $uuid)
    {
        $order = Order::with('sellerOrders.items.product')
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {

            $order = $this->orderService->cancel(
                $order,
                $request->user(),
                $data['reason'] ?? null
            );

        } catch (ValidationException $e) {

            return $this->error('Cannot cancel order', $e->errors(), 422);

        }

        return $this->success([
            'order' => new OrderResource(
                $order->load('user', 'sellerOrders.supplier', 'sellerOrders.items.product')
            ),
        ], 'Order cancelled.');
    }
}
