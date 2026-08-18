<?php

namespace App\Http\Requests\Dispute;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'reason' => ['required', Rule::in(Dispute::REASONS)],

            'description' => ['required', 'string', 'max:2000'],

            'images' => ['nullable', 'array', 'max:5'],

            'images.*' => ['image', 'max:5120'],

        ];
    }
}
