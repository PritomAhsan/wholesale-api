<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\InventoryTransactionType;

class IncreaseInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'transaction_type' => [
                'required',
                new Enum(InventoryTransactionType::class),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ];
    }
}
