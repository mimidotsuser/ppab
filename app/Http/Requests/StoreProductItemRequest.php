<?php

namespace App\Http\Requests;

use App\Utils\ProductTrackingUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreProductItemRequest extends FormRequest
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
    public function rules(Request $request)
    {
        //if the product is not on customer premises, it's in a warehouse
        return [
            'product_id' => ['required', 'exists:App\Models\Product,id'],
            'serial_number' => ['required', 'max:255', 'unique:App\Models\ProductItem,serial_number'],

            'customer_id' => ['required_without:warehouse_id', 'prohibited_unless:warehouse_id,null',
                'exists:App\Models\Customer,id'],
            'warehouse_id' => ['required_without:customer_id', 'prohibited_unless:customer_id,null',
                'exists:App\Models\Warehouse,id'],
            'purchase_order_id' => ['nullable', 'prohibited_unless:warehouse_id,null'], //TODO check exist on PO
            'warrant_start' => ['nullable', 'prohibited_unless:warehouse_id,null', 'date'],
            'warrant_end' => ['nullable', 'prohibited_unless:warehouse_id,null', 'date'],
            'contract_id' => ['nullable', 'prohibited_unless:warehouse_id,null',
                'exists:App\Models\CustomerContract,id'],
            'out_of_order' => ['required_without:customer_id', 'boolean',
                'prohibited_unless:customer_id,null'],
            //increment only if item is in good shape+ no PO(redundant just in case) + is in warehouse
            'increment_stock_by' => ['numeric', 'prohibited_unless:out_of_order,false',
                'prohibited_unless:purchase_order_id,null', 'prohibited_unless:customer_id,null'],
            'description' => ['nullable', 'max:255'],
            'category_code' => ['required',
                Rule::in(array_keys(ProductTrackingUtils::getLogCategories()))]
        ];
    }
}
