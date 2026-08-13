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

        ];
    }
}
