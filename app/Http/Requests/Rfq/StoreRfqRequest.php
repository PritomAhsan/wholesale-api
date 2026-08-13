<?php

namespace App\Http\Requests\Rfq;

use Illuminate\Foundation\Http\FormRequest;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'supplier_uuid' => ['nullable', 'string', 'exists:suppliers,uuid'],

            'product_uuid' => ['nullable', 'string', 'exists:products,uuid'],

            'product_name' => ['required', 'string', 'max:255'],

            'preferred_supplier_name' => ['nullable', 'string', 'max:255'],

            'quantity' => ['required', 'numeric', 'min:1'],

            'unit' => ['nullable', 'string', 'max:50'],

            'budget' => ['nullable', 'numeric', 'min:0'],

            'destination_country' => ['required', 'string', 'max:100'],

            'required_delivery_date' => ['nullable', 'date', 'after_or_equal:today'],

            'message' => ['required', 'string', 'max:5000'],

            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'],

            'contact_name' => ['required', 'string', 'max:255'],

            'contact_email' => ['required', 'email', 'max:255'],

            'contact_phone' => ['nullable', 'string', 'max:30'],

        ];
    }
}
