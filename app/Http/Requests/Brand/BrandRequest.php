<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return auth()->check();
        return true;
    }

    public function rules(): array
    {
        $brand = $this->route('brand');

        return [

            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')
                    ->ignore($brand),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'featured' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],

        ];
    }
}
