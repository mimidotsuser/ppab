<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProductItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create', ProductItem::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        //if the product is not on customer premises, it's in a warehouse
        return [
            'product_id' => ['required', Rule::exists(Product::class, 'id')],
            'serial_number' => ['required', 'max:255',
                Rule::unique(ProductItem::class, 'serial_number')],

            'customer_id' => ['required_without:warehouse_id', 'prohibited_unless:warehouse_id,null',
                Rule::exists(Customer::class, 'id')],
            'warehouse_id' => ['required_without:customer_id', 'prohibited_unless:customer_id,null',
                Rule::exists(Warehouse::class, 'id')],
            'purchase_order_id' => ['nullable', Rule::exists(PurchaseOrder::class, 'id')],
            'warrant_start' => ['nullable', 'prohibited_unless:warehouse_id,null', 'date'],
            'warrant_end' => ['nullable', 'prohibited_unless:warehouse_id,null', 'date'],
            'contract_id' => ['nullable', 'prohibited_unless:warehouse_id,null',
                Rule::exists(CustomerContract::class, 'id')],
            'out_of_order' => ['required_without:customer_id', 'boolean',
                'prohibited_unless:customer_id,null'],
            'description' => ['nullable', 'max:255'],
            'category_code' => ['required',
                Rule::in([ProductItemActivityUtils::activityCategoryCodes()['INITIAL_ENTRY']])]
        ];
    }
}
