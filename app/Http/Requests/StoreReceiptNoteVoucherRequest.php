<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ReceiptNoteVoucher;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreReceiptNoteVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        return Auth::user()->can('create', ReceiptNoteVoucher::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'warehouse_id' => ['required', Rule::exists(Warehouse::class, 'id')],
            'purchase_order_id' => ['required', Rule::exists(PurchaseOrder::class, 'id')],
            'reference' => 'required',
            'remarks' => 'nullable|max:2500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.po_item_id' => ['required',
                Rule::exists(PurchaseOrderItem::class, 'id')],
            'items.*.delivered_qty' => 'required|numeric|integer|min:0',
        ];
    }
}
