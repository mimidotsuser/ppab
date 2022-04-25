<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\ProductItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCustomerContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('customer_contract'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_id' => ['required', Rule::exists(Customer::class, 'id')],
            'category_code' => ['required', 'max:250'],
            'start_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:start_date'],
            'contract_items' => ['array'],
            'contract_items.*.product_item_id' => ['required',
                Rule::exists(ProductItem::class, 'id')],
        ];
    }
}
