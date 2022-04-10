<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', Company::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', Rule::unique(Company::class, 'name'), 'max:250'],
            'street_address' => 'required|max:250',
            'postal_address' => 'required|max:250',
            'telephone' => 'required|max:250',
            'mobile_phone' => 'required|max:250',
            'website' => 'required|max:250',
            'logo_url' => 'nullable|max:250'
        ];
    }
}
