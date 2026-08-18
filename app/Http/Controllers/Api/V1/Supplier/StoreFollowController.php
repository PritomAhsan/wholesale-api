<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Supplier\SupplierPublicResource;
use App\Models\StoreFollow;
use App\Models\Supplier;
use Illuminate\Http\Request;

class StoreFollowController extends ApiController
{
    /**
     * Toggle the authenticated buyer's follow state for a store.
     */
    public function toggle(Request $request, string $sellerId)
    {
        $supplier = Supplier::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->firstOrFail();

        $user = $request->user();

        $follow = StoreFollow::where('user_id', $user->id)
            ->where('supplier_id', $supplier->id)
            ->first();

        if ($follow) {

            $follow->delete();

            return $this->success(['following' => false], 'Store unfollowed.');
        }

        StoreFollow::create([

            'user_id' => $user->id,

            'supplier_id' => $supplier->id,

        ]);

        return $this->success(['following' => true], 'Store followed.');
    }

    /**
     * Stores the authenticated buyer currently follows.
     */
    public function mine(Request $request)
    {
        $user = $request->user();

        $supplierIds = StoreFollow::where('user_id', $user->id)->pluck('supplier_id');

        $suppliers = Supplier::whereIn('id', $supplierIds)
            ->where('status', 'approved')
            ->get();

        return $this->success([
            'sellers' => SupplierPublicResource::collection($suppliers),
        ]);
    }
}
