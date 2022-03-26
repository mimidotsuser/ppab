<?php

namespace App\Http\Requests;

use App\Models\ProductItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProductItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', $this->route('product_item'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => ['required', 'exists:App\Models\Product,id'],
            'serial_number' => ['required', 'max:255',
                Rule::unique(ProductItem::class, 'serial_number')
                    ->ignore($this->route('product_item'))],
            'purchase_order_id' => ['nullable'], //TODO check exist on PO

            //stock adjustment should be done only if PO does not exist
            'increment_stock_by' => ['numeric', 'prohibited_unless:purchase_order_id,null',],
        ];
    }
}
