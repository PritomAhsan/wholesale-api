<?php

namespace App\Http\Controllers\Api\V1\Review;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends ApiController
{
    public function index(Request $request, Product $product)
    {
        $reviews = $product->reviews()
            ->approved()
            ->with('user')
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->success([
            'reviews' => ReviewResource::collection($reviews),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Whether the authenticated buyer can review this product — has a
     * delivered order item for it and hasn't already reviewed it.
     */
    public function eligibility(Request $request, Product $product)
    {
        $user = $request->user();

        $alreadyReviewed = Review::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        $orderItem = $this->deliveredOrderItem($user->id, $product->id);

        return $this->success([
            'eligible' => ! $alreadyReviewed && $orderItem !== null,
            'already_reviewed' => $alreadyReviewed,
        ]);
    }

    public function store(StoreReviewRequest $request, Product $product)
    {
        $user = $request->user();

        if (Review::where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            return $this->error('You have already reviewed this product.', null, 422);
        }

        $orderItem = $this->deliveredOrderItem($user->id, $product->id);

        if (! $orderItem) {
            return $this->error(
                'Only buyers with a delivered order for this product can leave a review.',
                null,
                403
            );
        }

        $review = Review::create([

            'product_id' => $product->id,

            'user_id' => $user->id,

            'order_item_id' => $orderItem->id,

            'rating' => $request->validated('rating'),

            'comment' => $request->validated('comment'),

            'status' => 'approved',

        ]);

        return $this->success([
            'review' => new ReviewResource($review->load('user')),
        ], 'Review submitted.', 201);
    }

    private function deliveredOrderItem(int $userId, int $productId): ?OrderItem
    {
        return OrderItem::where('product_id', $productId)
            ->whereHas('sellerOrder', function ($query) use ($userId) {
                $query->where('status', 'delivered')
                    ->whereHas('order', fn ($q) => $q->where('user_id', $userId));
            })
            ->first();
    }
}
