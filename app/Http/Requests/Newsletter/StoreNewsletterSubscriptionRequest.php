<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'email' => ['required', 'email', 'max:255'],

            'topics' => ['nullable', 'array'],

            'topics.*' => ['string', 'in:inventory_opportunities,category_trends,buying_margin_guidance,platform_supplier_updates'],

            'frequency' => ['nullable', 'string', 'in:weekly,twice_monthly'],

        ];
    }
}
