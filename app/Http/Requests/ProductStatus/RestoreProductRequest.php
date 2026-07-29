<?php

namespace App\Http\Requests\ProductStatus;

use Illuminate\Foundation\Http\FormRequest;

class RestoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ];
    }
}
