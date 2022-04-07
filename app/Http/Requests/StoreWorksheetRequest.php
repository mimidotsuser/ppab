<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductItem;
use App\Utils\WorksheetUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorksheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'reference' => 'required',
            'customer_id' => ['required', Rule::exists(Customer::class, 'id')],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*' => 'required',
            'entries.*.category_code' => ['required',
                Rule::in(array_keys(WorksheetUtils::worksheetCategoryCodes()))],
            'entries.*.product_items.*.id' => ['required', 'min:1',
                Rule::exists(ProductItem::class, 'id')],

            'entries.*.repair_items' => ['array',
                Rule::requiredIf(function () {
                    return $this->request->get('category_code') ==
                        WorksheetUtils::worksheetCategoryCodes()['REPAIR'];
                })
            ],
            'entries.*.repair_items.*.product_id' => ['required',
                Rule::exists(Product::class, 'id')],
            'entries.*.repair_items.*.old_total' => 'required|numeric|min:0',
            'entries.*.repair_items.*.new_total' => 'required|numeric|min:0',
            'entries.*.description' => 'required|max:6000',
        ];
    }

    public function messages()
    {
        return [
            'entries.*.category_code.in' => 'Invalid category code (at entry :index)',
            'entries.*.product_items.*.id.exists' => 'Invalid product item identifier(:index)',
            'entries.*.repair_items.required_if' => 'Spare parts used required at entry :index',
            'entries.*.repair_items.*.product_id.exists' =>
                'Spare part does not exists( at entry :index , spare part :position)',
            'entries.*.description.required' => 'Entry at index :index has no description'
        ];
    }
}
