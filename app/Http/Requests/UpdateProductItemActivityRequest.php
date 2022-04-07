<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Warehouse;
use App\Utils\ProductItemActivityUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductItemActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('product_item_activity'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $categories = array_keys(ProductItemActivityUtils::activityCategoryCodes());
        return [
            'description' => 'required|max:250',
            'category_code' => ['required', Rule::in($categories)],

            'warrant_start' => ['nullable', 'date', Rule::requiredIf(function () {
                return $this->request->get('category_code') ==
                    ProductItemActivityUtils::activityCategoryCodes()['WARRANTY_UPDATE'];
            })],
            'warrant_end' => 'nullable|date|required_unless:warrant_start,null',

            'out_of_order' => 'nullable|boolean|required_unless:warehouse_id,null',
            'warehouse_id' => ['nullable', Rule::exists(Warehouse::class, 'id'),
                Rule::requiredIf(function () {
                    return $this->request->get('category_code') ==
                        ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_WAREHOUSE_TRANSFER'];
                })],

            'customer_id' => ['nullable', Rule::exists(Customer::class, 'id'),
                Rule::requiredIf(function () {
                    return $this->request->get('category_code') ==
                        ProductItemActivityUtils::activityCategoryCodes()['CUSTOMER_TO_CUSTOMER_TRANSFER'];
                })],
        ];
    }

}
