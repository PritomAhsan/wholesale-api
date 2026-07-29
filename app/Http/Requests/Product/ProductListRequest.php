<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'string',
            ],

            'supplier' => [
                'nullable',
                'uuid',
            ],

            'brand' => [
                'nullable',
                'uuid',
            ],

            'category' => [
                'nullable',
                'uuid',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'has_inventory' => [
                'nullable',
                'boolean',
            ],

            'sort' => [
                'nullable',
                'in:name,price,stock,created_at',
            ],

            'direction' => [
                'nullable',
                'in:asc,desc',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            'created_from' => [
                'nullable',
                'date',
            ],

            'created_to' => [
                'nullable',
                'date',
            ],

            'sort' => [

                'nullable',

                'in:name,price,stock,created_at,updated_at',

            ],

            'direction' => [

                'nullable',

                'in:asc,desc',

            ],

        ];
    }
}
