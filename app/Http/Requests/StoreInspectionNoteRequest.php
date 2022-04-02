<?php

namespace App\Http\Requests;

use App\Models\InspectionNote;
use App\Models\ReceiptNoteVoucher;
use App\Models\ReceiptNoteVoucherItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreInspectionNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', InspectionNote::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'receipt_note_voucher_id' => ['required',
                Rule::exists(ReceiptNoteVoucher::class, 'id')],
            'remarks' => 'required|max:250',
            'items' => 'required|array|min:1',
            'items.*.item_id' => ['required',
                Rule::exists(ReceiptNoteVoucherItem::class, 'id')],
            'items*.rejected_qty' => ['required', 'min:0'],
            'checklist' => 'required|array|min:1',
            'checklist.*.feature' => 'required|max:250',
            'checklist.*.passed' => 'required|boolean',
        ];
    }
}
