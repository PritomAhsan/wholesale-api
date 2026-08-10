<?php

namespace App\Http\Requests\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'product_id' => [

                'required',

                'exists:products,id',

            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_variants', 'sku')
                    ->ignore($this->route('variant_id')),
            ],

            'barcode' => [

                'nullable',

                'string',

                'max:100',

            ],

            'cost_price' => [

                'required',

                'numeric',

                'min:0',

            ],

            'selling_price' => [

                'required',

                'numeric',

                'gte:cost_price',

            ],

            'compare_at_price' => [

                'nullable',

                'numeric',

                'gte:selling_price',

            ],

            'stock_quantity' => [

                'required',

                'integer',

                'min:0',

            ],

            'low_stock_quantity' => [

                'nullable',

                'integer',

                'min:0',

            ],

            'weight' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            'length' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            'width' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            'height' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            'is_active' => [

                'boolean',

            ],

            'attributes' => [

                'required',

                'array',

                'min:1',

            ],

            'attributes.*.attribute_id' => [

                'required',

                'exists:attributes,id',

            ],

            'attributes.*.attribute_value_id' => [

                'required',

                'exists:attribute_values,id',

            ],

            'is_default' => [

                'sometimes',

                'boolean',

            ],

            'sort_order' => [

                'sometimes',

                'integer',

                'min:0',

            ],

            'wholesale_price' => [

                'nullable',

                'numeric',

                'min:0',

                'lte:selling_price',

            ],

            'minimum_order_quantity' => [

                'required',

                'numeric',

                'min:1',

            ],

            'maximum_order_quantity' => [

                'nullable',

                'numeric',

                'gte:minimum_order_quantity',

            ],

        ];
    }
}
