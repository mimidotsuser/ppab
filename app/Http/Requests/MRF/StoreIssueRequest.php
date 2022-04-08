<?php

namespace App\Http\Requests\MRF;

use App\Models\MaterialRequisitionItem;
use App\Models\ProductItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('issue', $this->route('material_requisition'));
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
            'items' => 'required|array|min:1',
            'items.spares' => 'requiredIf:machines,null|nullable|array',
            'items.spares.*.item_id' => ['required',
                Rule::exists(MaterialRequisitionItem::class, 'id')],
            'items.spares.*.old_total' => ['required', 'numeric', 'integer', 'min:0'],
            'items.spares.*.new_total' => ['required', 'numeric', 'integer', 'min:0'],

            'items.machines' => 'requiredIf:spares,null|nullable|array',
            'items.machines.*.item_id' => ['required',
                Rule::exists(MaterialRequisitionItem::class, 'id')],
            'items.machines.*.allocation' => ['required', 'array'],
            'items.machines.*.allocation.*.product_item_id' => ['required',
                Rule::exists(ProductItem::class, 'id')],
            'items.machines.*.allocation.*.warrant_start' => ['nullable', 'date',
                'required_unless:warrant_end,null'],
            'items.machines.*.allocation.*.warrant_end' => ['nullable', 'date']
        ];
    }
}
