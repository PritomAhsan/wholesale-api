<?php

namespace App\Http\Requests\Product;

use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return auth()->check();
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'brand_id' => [
                'nullable',
                'exists:brands,id',
            ],

            'unit_id' => [
                'nullable',
                'exists:units,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                Rule::unique('products', 'slug')
                    ->ignore($product),
            ],

            'sku' => [
                'nullable',
                'string',
                Rule::unique('products', 'sku')
                    ->ignore($product),
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

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

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */

            'min_order_quantity' => [
                'required',
                'numeric',
                'min:1',
            ],

            'max_order_quantity' => [
                'nullable',
                'numeric',
                'gte:min_order_quantity',
            ],

            'stock_quantity' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Dimensions
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Flags
            |--------------------------------------------------------------------------
            */

            'featured' => [
                'sometimes',
                'boolean',
            ],

            'is_digital' => [
                'sometimes',
                'boolean',
            ],

            'requires_shipping' => [
                'sometimes',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => [
                'nullable',
                Rule::in(ProductStatus::values()),
            ],

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
            ],

            'category_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'category_ids.*' => [
                'integer',
                'exists:categories,id',
            ],

            'attributes' => [
                'nullable',
                'array',
            ],

            'attributes.*.attribute_id' => [
                'required',
                'exists:attributes,id',
            ],

            'attributes.*.attribute_value_id' => [
                'required',
                'exists:attribute_values,id',
            ],

            /*
|--------------------------------------------------------------------------
| Existing Images
|--------------------------------------------------------------------------
*/

'deleted_image_ids' => [

    'nullable',

    'array',

],

'deleted_image_ids.*' => [

    'integer',

    'exists:product_images,id',

],

'primary_image_id' => [

    'nullable',

    'integer',

    'exists:product_images,id',

],

/*
|--------------------------------------------------------------------------
| New Images
|--------------------------------------------------------------------------
*/

'images' => [

    'nullable',

    'array',

],

'images.*' => [

    'image',

    'mimes:jpg,jpeg,png,webp',

    'max:5120',

],

'image_alt_texts' => [

    'nullable',

    'array',

],

'image_alt_texts.*' => [

    'nullable',

    'string',

    'max:255',

],
        ];
    }

    public function messages(): array
    {
        return [

            'selling_price.gte' =>
                'Selling price must be greater than or equal to cost price.',

            'compare_at_price.gte' =>
                'Compare at price must be greater than or equal to selling price.',

            'max_order_quantity.gte' =>
                'Maximum order quantity must be greater than minimum order quantity.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'featured' => $this->boolean('featured'),

            'is_digital' => $this->boolean('is_digital'),

            'requires_shipping' => $this->boolean('requires_shipping'),

        ]);
    }
}
