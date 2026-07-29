<?php

namespace App\Http\Requests\ProductImage;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'images' => [

                'required',

                'array',

                'min:1',

            ],

            'images.*.uuid' => [

                'required',

                'exists:product_images,uuid',

            ],

            'images.*.sort_order' => [

                'required',

                'integer',

                'min:0',

            ],

        ];
    }
}
