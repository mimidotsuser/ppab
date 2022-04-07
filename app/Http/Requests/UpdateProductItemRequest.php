<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductItem;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
        $morphKey = key(Arr::where(Relation::morphMap(), fn($key) => $key == Warehouse::class));
        $itemInWarehouse=  $this->route('product_item')->latestActivity->location_type == $morphKey;

        return [
            'product_id' => ['required', Rule::exists(Product::class, 'id')],
            'serial_number' => ['required', 'max:255',
                Rule::unique(ProductItem::class, 'serial_number')
                    ->ignore($this->route('product_item'))],
            'purchase_order_id' => ['nullable', Rule::exists(PurchaseOrder::class, 'id')],

            'out_of_order' => ['boolean', Rule::when(!$itemInWarehouse,'prohibited')],
        ];
    }
}
