<?php

namespace App\Http\Requests;

use App\Utils\CustomerUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => 'required|exists:App\Models\Customer:id',
            'category_code' => ['required', Rule::in(array_keys(CustomerUtils::getContractTypes()))],
            'start_date' => 'required',
            'expiry_date' => 'required',
            'contract_items' => 'sometimes|array',
            'contract_items.*.id' => 'exists:App\Models\ProductTracking,id',
        ];
    }
}
