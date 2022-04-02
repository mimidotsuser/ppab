<?php

namespace App\Http\Requests;

use App\Models\GoodsReceiptNoteItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateInspectionNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('inspection_note'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remarks' => 'required|max:250',
            'items' => 'sometimes|array|min:1',
            'items.*.item_id' => ['required',
                Rule::exists(GoodsReceiptNoteItem::class, 'id')],
            'items*.rejected_qty' => ['required', 'min:0'],
            'checklist' => 'sometimes|array|min:1',
            'checklist.*.feature' => 'required|max:250',
            'checklist.*.passed' => 'required|boolean',
        ];
    }
}
