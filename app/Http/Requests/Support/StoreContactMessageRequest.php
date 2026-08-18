<?php

namespace App\Http\Requests\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'topic' => ['required', 'string', 'in:buyer_support,supplier_onboarding,order_dispute,compliance_restricted_products'],

            'name' => ['required', 'string', 'max:255'],

            'business_email' => ['required', 'email', 'max:255'],

            'account_email' => ['nullable', 'email', 'max:255'],

            'reference_number' => ['nullable', 'string', 'max:100'],

            'message' => ['required', 'string', 'max:5000'],

            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,png,jpg,jpeg'],

        ];
    }
}
