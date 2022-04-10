<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\ProductItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
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
        return Auth::user()->can('create', CustomerContract::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::exists(Customer::class, 'id')],
            'category_code' => ['required', 'max:250'],
            'category_title' => ['required', 'max:250'],
            'start_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after:start_date'],
            'contract_items' => ['required', 'array', 'min:1'],
            'contract_items.*.product_item_id' => ['required',
                Rule::exists(ProductItem::class, 'id')],
        ];
    }
}
