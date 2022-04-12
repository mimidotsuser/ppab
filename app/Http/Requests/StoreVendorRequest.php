<?php

namespace App\Http\Requests;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreVendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', Vendor::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:250',
            'address' => 'nullable|max:250',
            'telephone' => 'nullable|max:250',
            'email' => 'nullable|email|max:250',
            'street_address' => 'nullable|email|max:250',
            'mobile_phone' => 'nullable|email|max:250',
            'postal_address' => 'nullable|email|max:250',
            'contactPersons' => 'nullable|array',
            'contactPersons.*.first_name' => 'required|max:250',
            'contactPersons.*.last_name' => 'nullable|max:250',
            'contactPersons.*.email' => 'nullable|email|required_if:contactPersons.*.mobile_phone,null|max:250',
            'contactPersons.*.mobile_phone' => 'nullable|required_if:contactPersons.*.email,null|max:250',

        ];
    }
}
