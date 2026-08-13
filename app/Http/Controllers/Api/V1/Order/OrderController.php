<?php

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Order\StoreCheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends ApiController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Checkout — create an order (and split it per seller) from a cart.
     */
    public function store(StoreCheckoutRequest $request)
    {
        try {

            $order = $this->orderService->checkout(
                $request->user(),
                $request->validated()['items'],
                $request->validated()['shipping']
            );

            return $this->success([
                'order' => new OrderResource($order),
            ], 'Order placed successfully.', 201);

        } catch (ValidationException $e) {

            return $this->error(
                'Checkout failed',
                $e->errors(),
                422
            );

        }
    }

    /**
     * The authenticated buyer's order history.
     */
    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('sellerOrders.supplier')
            ->latest()
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
     * A single order belonging to the authenticated buyer.
     */
    public function show(Request $request, string $uuid)
    {
        $order = $request->user()
            ->orders()
            ->with('sellerOrders.supplier', 'sellerOrders.items.product')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->success([
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Cancel one of the authenticated buyer's own orders.
     */
    public function cancel(Request $request, string $uuid)
    {
        $order = $request->user()
            ->orders()
            ->with('sellerOrders.items.product')
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
            'order' => new OrderResource($order->load('sellerOrders.supplier')),
        ], 'Order cancelled.');
    }
}
