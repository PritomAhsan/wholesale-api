<?php

namespace App\Http\Requests\ProductImage;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImagesRequest extends FormRequest
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

                'max:20',

            ],

            'images.*' => [

                'required',

                'image',

                'mimes:jpg,jpeg,png,webp',

                'max:5120',

            ],

            'alt_text' => [

                'nullable',

                'array',

            ],

            'alt_text.*' => [

                'nullable',

                'string',

                'max:255',

            ],

        ];
    }
}
