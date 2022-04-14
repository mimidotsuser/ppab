<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\ProductItemActivity;
use App\Models\Warehouse;
use App\Utils\MRFUtils;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProductItemActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', ProductItemActivity::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {

        $categories = array_keys(ProductItemActivityUtils::activityCategoryCodes());

        $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));
        //cannot update warrant if item is  not in a customer premise
        $isInWarehouse = $this->route('product_item')->latestActivity->location_type == $morphKey;

        $movingToWarehouse = $request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_WAREHOUSE_TRANSFER']
            || $request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['WAREHOUSE_TO_WAREHOUSE_TRANSFER'];

        $isCustomerToCustomerTransfer = $request->get('category_code') ===
            ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_CUSTOMER_TRANSFER'];

        $isWarrantyUpdate = $this->request->get('category_code') ==
            ProductItemActivityUtils::activityCategoryCodes()['WARRANTY_UPDATE'];

        return [
            'description' => 'required|max:250',
            'category_code' => ['required', Rule::in($categories)],

            'warrant_end' => 'nullable|date|prohibited_if:warrant_start,null',
            'warrant_start' => ['nullable', 'date', Rule::requiredIf($isWarrantyUpdate),
                Rule::when($isInWarehouse, 'prohibited')
            ],

            'out_of_order' => 'nullable|boolean|prohibited_if:warehouse_id,null',
            'warehouse_id' => ['nullable', Rule::requiredIf(fn() => $movingToWarehouse),
                Rule::exists(Warehouse::class, 'id'),
                Rule::when(
                    $isInWarehouse || $isCustomerToCustomerTransfer || $isWarrantyUpdate,
                    'prohibited'),
            ],

            'customer_id' => [Rule::exists(Customer::class, 'id'),
                Rule::requiredIf($isCustomerToCustomerTransfer),
                Rule::when($isInWarehouse, 'prohibited')],
            'purpose_code' => [Rule::in(array_keys(MRFUtils::purpose())),
                Rule::requiredIf($isCustomerToCustomerTransfer),
                Rule::when(!$isCustomerToCustomerTransfer, 'prohibited')]
        ];
    }

    public function messages()
    {
        return [
            'customer_id.prohibited' => 'Action prohibited as item is currently in warehouse premisses',
            'warehouse_id.prohibited' => 'Action prohibited as item is currently in warehouse premisses'
        ];
    }
}
