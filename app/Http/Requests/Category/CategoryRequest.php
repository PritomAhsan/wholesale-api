<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // return auth()->check();
        return true;
    }

    public function rules(): array
    {
        return [

            'parent_id' => [
                'nullable',
                'exists:categories,id'
            ],

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'description' => [
                'nullable',
                'string'
            ],

            'sort_order' => [
                'nullable',
                'integer'
            ],

            'status' => [
                'boolean'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'icon' => [
                'nullable',
                'image',
                'mimes:png,svg,webp',
                'max:1024',
            ],

        ];
    }
}
