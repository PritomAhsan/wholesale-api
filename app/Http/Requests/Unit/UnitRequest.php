<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $unit = $this->route('unit');

        return [

            'name' => [

                'required',

                'string',

                'max:255',

                Rule::unique('units')
                    ->ignore($unit),

            ],

            'code' => [

                'required',

                'string',

                'max:20',

                Rule::unique('units')
                    ->ignore($unit),

            ],

            'symbol' => [

                'nullable',

                'string',

                'max:20',

            ],

            'description' => [

                'nullable',

                'string',

            ],

            'sort_order' => [

                'nullable',

                'integer',

                'min:0',

            ],

            'status' => [

                'nullable',

                'boolean',

            ],

        ];
    }
}
