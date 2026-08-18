<?php

namespace App\Services\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

/**
 * Computes a 0-100 seller verification score and trust badges from
 * real, measurable signals only. The framework spec (§22) also lists
 * "low dispute rate", "fast response time" and "on-time shipping" as
 * factors — none of those have a real data source in this system yet
 * (no dispute model, no messaging, no promised-delivery date to
 * compare against), so they're deliberately left out of the weighting
 * rather than defaulted to a flattering score. The five factors below
 * are the ones this system can actually measure.
 */
class SellerVerificationScoreService
{
    public function scoreFor(Supplier $supplier): array
    {
        $stats = $this->stats($supplier);

        $verifiedPoints = $supplier->status === 'approved' ? 20 : 0;

        $completionPoints = $stats['completion_rate'] !== null
            ? (int) round($stats['completion_rate'] * 25)
            : 0;

        $reviewPoints = $stats['avg_rating'] !== null
            ? (int) round(($stats['avg_rating'] / 5) * 25)
            : 0;

        $repeatPoints = $stats['repeat_buyer_rate'] !== null
            ? (int) round($stats['repeat_buyer_rate'] * 15)
            : 0;

        $approvalPoints = $stats['approval_quality'] !== null
            ? (int) round($stats['approval_quality'] * 15)
            : 0;

        $score = $verifiedPoints + $completionPoints + $reviewPoints + $repeatPoints + $approvalPoints;

        return [
            'score' => $score,
            'badges' => $this->badgesFor($supplier, $stats),
            'completed_order_count' => $stats['delivered_orders'],
            'review_count' => $stats['review_count'],
            'avg_rating' => $stats['avg_rating'],
        ];
    }

    protected function stats(Supplier $supplier): array
    {
        $delivered = $supplier->sellerOrders()->where('status', 'delivered')->count();
        $cancelled = $supplier->sellerOrders()->where('status', 'cancelled')->count();
        $concluded = $delivered + $cancelled;

        $submittedProducts = $supplier->products()
            ->whereIn('status', ['pending', 'approved', 'published', 'rejected'])
            ->count();

        $approvedProducts = $supplier->products()
            ->whereIn('status', ['approved', 'published'])
            ->count();

        $buyerDeliveries = DB::table('seller_orders')
            ->join('orders', 'orders.id', '=', 'seller_orders.order_id')
            ->where('seller_orders.supplier_id', $supplier->id)
            ->where('seller_orders.status', 'delivered')
            ->select('orders.user_id', DB::raw('count(*) as delivered_orders'))
            ->groupBy('orders.user_id')
            ->get();

        $distinctBuyers = $buyerDeliveries->count();
        $repeatBuyers = $buyerDeliveries->where('delivered_orders', '>', 1)->count();

        $reviewCount = $supplier->storeReviews()->approved()->count();
        $avgRating = $reviewCount > 0
            ? (float) $supplier->storeReviews()->approved()->avg('rating')
            : null;

        return [
            'delivered_orders' => $delivered,
            'completion_rate' => $concluded > 0 ? $delivered / $concluded : null,
            'approval_quality' => $submittedProducts > 0 ? $approvedProducts / $submittedProducts : null,
            'distinct_buyers' => $distinctBuyers,
            'repeat_buyer_rate' => $distinctBuyers > 0 ? $repeatBuyers / $distinctBuyers : null,
            'review_count' => $reviewCount,
            'avg_rating' => $avgRating,
        ];
    }

    protected function badgesFor(Supplier $supplier, array $stats): array
    {
        $badges = [];

        if ($supplier->status === 'approved') {
            $badges[] = 'verified_store';
        }

        $memberSince = $supplier->approved_at ?? $supplier->created_at;

        if ($memberSince && $memberSince->gt(now()->subDays(30)) && $stats['delivered_orders'] === 0) {
            $badges[] = 'new_seller';
        }

        if ($stats['review_count'] >= 3 && $stats['avg_rating'] !== null && $stats['avg_rating'] >= 4.5) {
            $badges[] = 'top_rated_seller';
        }

        if ($stats['distinct_buyers'] >= 5 && $stats['repeat_buyer_rate'] !== null && $stats['repeat_buyer_rate'] >= 0.3) {
            $badges[] = 'high_repeat_buyer_rate';
        }

        $hasBulkDeal = $supplier->products()
            ->whereHas('deals', fn ($query) => $query->active()->where('type', 'bulk'))
            ->exists();

        if ($hasBulkDeal) {
            $badges[] = 'bulk_order_friendly';
        }

        return $badges;
    }
}
