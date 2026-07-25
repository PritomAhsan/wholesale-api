<?php

namespace App\Http\Requests\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductAttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $value = $this->route('product_attribute_value');

        return [

            'value' => [

                'required',

                'string',

                'max:255',

                Rule::unique('product_attribute_values')
                    ->ignore($value),

            ],

            'sort_order' => [

                'nullable',

                'integer',

                'min:0',

            ],

            'status' => [

                'nullable',

                'boolean',

            ],

        ];
    }
}
