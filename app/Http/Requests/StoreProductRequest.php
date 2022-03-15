<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'parent_id' => 'nullable|exists:App\Models\Product,id',
            'product_category_id' => 'required|exists:App\Models\ProductCategory,id',
            'item_code' => 'nullable|max:255|unique:App\Models\Product,item_code',
            'manufacturer_part_number' => 'nullable|max:255',
            'description' => 'required|string|max:255',
            'local_description' => 'nullable|max:255',
            'chinese_description' => 'nullable|max:255',
            'economic_order_qty' => 'required|numeric|min:0',
            'min_level' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
            'max_level' => 'required|numeric|min:0',
            'create_old_variant' => 'required_with:parent_id|boolean'
        ];
    }
}
