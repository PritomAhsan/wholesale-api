<?php

namespace App\Http\Requests\Deal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'product_id' => ['required', 'exists:products,id'],

            'type' => ['required', Rule::in(['flash', 'bulk', 'clearance'])],

            'title' => ['required', 'string', 'max:255'],

            'description' => ['nullable', 'string'],

            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:100'],

            'discount_price' => ['nullable', 'numeric', 'min:0'],

            'min_quantity' => ['nullable', 'integer', 'min:1'],

            'starts_at' => ['nullable', 'date'],

            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],

            'status' => ['sometimes', Rule::in(['active', 'inactive'])],

        ];
    }
}
