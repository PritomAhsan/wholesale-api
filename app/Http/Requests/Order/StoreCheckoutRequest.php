<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_uuid' => ['required', 'string'],

            'items.*.quantity' => ['required', 'integer', 'min:1'],

            'shipping' => ['required', 'array'],

            'shipping.name' => ['required', 'string', 'max:255'],

            'shipping.phone' => ['required', 'string', 'max:30'],

            'shipping.address' => ['required', 'string', 'max:500'],

            'shipping.city' => ['required', 'string', 'max:100'],

            'shipping.country' => ['required', 'string', 'max:100'],

            'shipping.postal_code' => ['nullable', 'string', 'max:20'],

            'shipping.notes' => ['nullable', 'string', 'max:1000'],

            // Selected live rate from /shipping/rates (Phase 18 demo, off by
            // default). Optional — checkout works exactly as before when
            // Shippo isn't enabled or the buyer didn't pick a rate.
            'shipping_rate' => ['nullable', 'array'],

            'shipping_rate.carrier' => ['required_with:shipping_rate', 'string', 'max:100'],

            'shipping_rate.service' => ['required_with:shipping_rate', 'string', 'max:100'],

            'shipping_rate.rate' => ['required_with:shipping_rate', 'numeric', 'min:0'],

        ];
    }
}
