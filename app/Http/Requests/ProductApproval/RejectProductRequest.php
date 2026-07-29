<?php

namespace App\Http\Requests\ProductApproval;

use Illuminate\Foundation\Http\FormRequest;

class RejectProductRequest extends FormRequest
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
