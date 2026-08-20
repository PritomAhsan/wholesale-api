<?php

namespace App\Services\Shipping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippoRateService
{
    /**
     * @param  array{name?: string, street1: string, city: string, state?: string, zip: string, country: string}  $destination
     * @param  array{weight: float, length: float, width: float, height: float}  $parcel  weight in kg, dimensions in cm
     * @return array<int, array{carrier: string, service: string, rate: float, currency: string, delivery_days: int|null}>
     */
    public function getRates(array $destination, array $parcel): array
    {
        $from = config('services.shippo.from_address');
        $token = config('services.shippo.token');

        $response = Http::withHeaders([
                'Authorization' => "ShippoToken {$token}",
            ])
            ->timeout(15)
            ->post('https://api.goshippo.com/shipments/', [
                'address_from' => [
                    'name' => $from['name'],
                    'street1' => $from['street1'],
                    'city' => $from['city'],
                    'state' => $from['state'],
                    'zip' => $from['zip'],
                    'country' => $from['country'],
                ],
                'address_to' => [
                    'name' => $destination['name'] ?? 'Customer',
                    'street1' => $destination['street1'],
                    'city' => $destination['city'],
                    'state' => $destination['state'] ?? '',
                    'zip' => $destination['zip'],
                    'country' => $destination['country'],
                ],
                'parcels' => [[
                    'length' => $parcel['length'],
                    'width' => $parcel['width'],
                    'height' => $parcel['height'],
                    'distance_unit' => 'cm',
                    'weight' => $parcel['weight'],
                    'mass_unit' => 'kg',
                ]],
                'async' => false,
            ]);

        if (! $response->successful()) {
            Log::warning('Shippo rate request failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [];
        }

        $rates = $response->json('rates') ?? [];

        return collect($rates)
            ->map(fn (array $rate) => [
                'carrier' => $rate['provider'],
                'service' => $rate['servicelevel']['name'] ?? $rate['servicelevel_name'] ?? 'Standard',
                'rate' => (float) $rate['amount'],
                'currency' => $rate['currency'],
                'delivery_days' => $rate['estimated_days'] ?? null,
            ])
            ->sortBy('rate')
            ->values()
            ->all();
    }
}
