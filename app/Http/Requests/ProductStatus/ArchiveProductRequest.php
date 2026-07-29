<?php

namespace App\Http\Requests\ProductStatus;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'remarks' => [
                'required',
                'string',
                'max:1000',
            ],

        ];
    }
}
