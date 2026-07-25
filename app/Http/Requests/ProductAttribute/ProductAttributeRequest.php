<?php

namespace App\Http\Requests\ProductAttribute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $attribute = $this->route('product_attribute');

        return [

            'category_id' => [
                'nullable',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_attributes', 'name')
                    ->ignore($attribute),
            ],

            'type' => [
                'required',
                Rule::in([
                    'text',
                    'number',
                    'select',
                    'multiselect',
                    'boolean',
                ]),
            ],

            'is_filterable' => [
                'nullable',
                'boolean',
            ],

            'is_required' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
