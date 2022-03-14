<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('customer'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'parent_id' => 'sometimes|exists:App\Models\Customer,id',
            'name' => ['sometimes', 'string', 'max:255',
                Rule::unique('App\Models\Customer')
                    ->where('branch', $request->get('branch'))
                    ->where('region', $request->get('region'))
                    ->ignore($this->route('customer'))
            ],
            'branch' => 'sometimes|string|max:255',
            'region' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
        ];
    }
}
