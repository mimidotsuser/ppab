<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('product'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = $this->route('product');
        return [
            'parent_id' => 'nullable|exists:App\Models\Product,id',
            'product_category_id' => 'sometimes|exists:App\Models\ProductCategory,id',
            'item_code' => ['sometimes', 'max:255',
                Rule::unique('App\Models\Product', 'item_code')
                    ->ignore($product->internal_code, 'internal_code')
            ],
            'manufacturer_part_number' => 'nullable|max:255',
            'description' => 'required|string|max:255',
            'local_description' => 'nullable|max:255',
            'chinese_description' => 'nullable|max:255',
            'economic_order_qty' => 'sometimes|numeric|min:0',
            'min_level' => 'sometimes|numeric|min:0',
            'reorder_level' => 'sometimes|numeric|min:0',
            'max_level' => 'sometimes|numeric|min:0',
        ];
    }
}
