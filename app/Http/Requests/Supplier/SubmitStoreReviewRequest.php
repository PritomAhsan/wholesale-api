<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SubmitStoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'rating' => ['required', 'integer', 'min:1', 'max:5'],

            'communication_rating' => ['required', 'integer', 'min:1', 'max:5'],

            'shipping_rating' => ['required', 'integer', 'min:1', 'max:5'],

            'packaging_rating' => ['required', 'integer', 'min:1', 'max:5'],

            'comment' => ['required', 'string', 'max:2000'],

        ];
    }
}
