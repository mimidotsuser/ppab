<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', Customer::class);
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
            'name' => ['required', 'string', 'max:255',
                Rule::unique('App\Models\Customer')
                    ->where('branch', $request->get('branch'))
                    ->where('region', $request->get('region'))],
            'branch' => 'sometimes|string|max:255',
            'region' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'A similar customer already exists with same name on same region and branch'
        ];
    }
}
