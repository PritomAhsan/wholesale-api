<?php

namespace App\Http\Requests\Dispute;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'resolution' => ['required', Rule::in(Dispute::RESOLUTIONS)],

            'resolution_amount' => ['required_if:resolution,refund_partial', 'nullable', 'numeric', 'min:0.01'],

            'resolution_note' => ['nullable', 'string', 'max:2000'],

        ];
    }
}
