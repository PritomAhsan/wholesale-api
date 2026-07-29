<?php

namespace App\Http\Requests\ProductApproval;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProductForReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'remarks' => [

                'nullable',

                'string',

                'max:1000',

            ],

        ];
    }
}
