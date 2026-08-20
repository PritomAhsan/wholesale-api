<?php

namespace App\Http\Controllers\Api\V1\Shipping;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Shipping\ShippingRateRequest;
use App\Models\Product;
use App\Services\Shipping\ShippoRateService;

class ShippingRateController extends ApiController
{
    // Demo defaults for products that haven't had real weight/dimensions
    // entered yet (Phase 18's known dependency — these fields are still
    // optional on the product form). Roughly a small parcel box, in kg/cm.
    private const FALLBACK_WEIGHT_KG = 1.0;
    private const FALLBACK_LENGTH_CM = 25.0;
    private const FALLBACK_WIDTH_CM = 20.0;
    private const FALLBACK_HEIGHT_CM = 15.0;

    public function quote(ShippingRateRequest $request, ShippoRateService $rates)
    {
        if (! config('services.shippo.enabled') || ! config('services.shippo.token')) {
            return $this->success(['enabled' => false, 'rates' => []]);
        }

        $data = $request->validated();

        $products = Product::whereIn('uuid', collect($data['items'])->pluck('product_uuid'))
            ->get()
            ->keyBy('uuid');

        $totalWeightKg = 0;
        $maxLengthCm = self::FALLBACK_LENGTH_CM;
        $maxWidthCm = self::FALLBACK_WIDTH_CM;
        $maxHeightCm = self::FALLBACK_HEIGHT_CM;

        foreach ($data['items'] as $item) {

            $product = $products->get($item['product_uuid']);

            if (! $product) {
                continue;
            }

            $weight = (float) ($product->weight ?? self::FALLBACK_WEIGHT_KG);
            $totalWeightKg += $weight * $item['quantity'];

            $maxLengthCm = max($maxLengthCm, (float) ($product->length ?? self::FALLBACK_LENGTH_CM));
            $maxWidthCm = max($maxWidthCm, (float) ($product->width ?? self::FALLBACK_WIDTH_CM));
            $maxHeightCm = max($maxHeightCm, (float) ($product->height ?? self::FALLBACK_HEIGHT_CM));

        }

        $parcel = [
            // Shippo accepts kg/cm directly via mass_unit/distance_unit.
            'weight' => round(max($totalWeightKg, self::FALLBACK_WEIGHT_KG), 2),
            'length' => round($maxLengthCm, 2),
            'width' => round($maxWidthCm, 2),
            'height' => round($maxHeightCm, 2),
        ];

        $result = $rates->getRates($data['destination'], $parcel);

        return $this->success(['enabled' => true, 'rates' => $result]);
    }
}
