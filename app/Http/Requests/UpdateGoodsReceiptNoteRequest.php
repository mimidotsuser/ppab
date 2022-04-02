<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateGoodsReceiptNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('goods_receipt_note'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'warehouse_id' => ['required', Rule::exists(Warehouse::class, 'id')],
            'purchase_order_id' => ['required',
                Rule::exists(PurchaseOrder::class, 'id')],
            'reference' => 'required',
            'remarks' => 'nullable|max:2500'
        ];
    }
}
