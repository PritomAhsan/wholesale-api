<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Supplier\SubmitStoreReviewRequest;
use App\Http\Resources\Supplier\StoreReviewResource;
use App\Models\SellerOrder;
use App\Models\StoreReview;
use App\Models\Supplier;
use Illuminate\Http\Request;

class StoreReviewController extends ApiController
{
    public function index(Request $request, string $sellerId)
    {
        $supplier = Supplier::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->firstOrFail();

        $reviews = $supplier->storeReviews()
            ->approved()
            ->with('user')
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'reviews' => StoreReviewResource::collection($reviews),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Whether the authenticated buyer can review this store — has a
     * delivered seller order from it and hasn't already reviewed it.
     */
    public function eligibility(Request $request, string $sellerId)
    {
        $supplier = Supplier::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->firstOrFail();

        $user = $request->user();

        $alreadyReviewed = StoreReview::where('supplier_id', $supplier->id)
            ->where('user_id', $user->id)
            ->exists();

        $sellerOrder = $this->deliveredSellerOrder($user->id, $supplier->id);

        return $this->success([
            'eligible' => ! $alreadyReviewed && $sellerOrder !== null,
            'already_reviewed' => $alreadyReviewed,
        ]);
    }

    public function store(SubmitStoreReviewRequest $request, string $sellerId)
    {
        $supplier = Supplier::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->firstOrFail();

        $user = $request->user();

        if (StoreReview::where('supplier_id', $supplier->id)->where('user_id', $user->id)->exists()) {
            return $this->error('You have already reviewed this store.', null, 422);
        }

        $sellerOrder = $this->deliveredSellerOrder($user->id, $supplier->id);

        if (! $sellerOrder) {
            return $this->error(
                'Only buyers with a delivered order from this store can leave a review.',
                null,
                403
            );
        }

        $review = StoreReview::create([

            'supplier_id' => $supplier->id,

            'user_id' => $user->id,

            'seller_order_id' => $sellerOrder->id,

            'rating' => $request->validated('rating'),

            'communication_rating' => $request->validated('communication_rating'),

            'shipping_rating' => $request->validated('shipping_rating'),

            'packaging_rating' => $request->validated('packaging_rating'),

            'comment' => $request->validated('comment'),

            'status' => 'approved',

        ]);

        return $this->success([
            'review' => new StoreReviewResource($review->load('user')),
        ], 'Review submitted.', 201);
    }

    private function deliveredSellerOrder(int $userId, int $supplierId): ?SellerOrder
    {
        return SellerOrder::where('supplier_id', $supplierId)
            ->where('status', 'delivered')
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId))
            ->first();
    }
}
