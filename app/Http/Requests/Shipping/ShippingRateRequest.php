<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class ShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'destination' => ['required', 'array'],

            'destination.street1' => ['required', 'string', 'max:500'],

            'destination.city' => ['required', 'string', 'max:100'],

            'destination.state' => ['nullable', 'string', 'max:100'],

            'destination.zip' => ['required', 'string', 'max:20'],

            'destination.country' => ['required', 'string', 'max:100'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.product_uuid' => ['required', 'string'],

            'items.*.quantity' => ['required', 'integer', 'min:1'],

        ];
    }
}
