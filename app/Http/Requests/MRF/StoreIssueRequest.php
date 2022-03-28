<?php

namespace App\Http\Requests\MRF;

use App\Models\MaterialRequisitionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
            'items.spares' => 'requiredIf:machines,null|array|min:1',
            'items.machines' => 'requiredIf:machines,null|array|min:1',
            'items.spares.*' => Rule::forEach(function () {
                return function ($attribute, $value, $fail) {

                    if (empty($value['id'])) {
                        return $fail($attribute . '.id field is required.');
                    }
                    if (!is_numeric($value['old_total'])) {
                        return $fail($attribute . '.old_total field is required.');
                    }
                    if (!is_numeric($value['new_total'])) {
                        return $fail($attribute . '.new_total field is required.');
                    }

                    $model = MaterialRequisitionItem::without(['customer', 'product'])
                        ->whereBelongsTo($this->route('material_requisition'), 'request')
                        ->find($value['id']);

                    if (empty($model)) {
                        return $fail($attribute . '.id item does not exists');
                    }

                    if ($model->approved_qty < $value['old_total'] + $value['new_total']) {
                        return $fail($attribute . ' exceeds quantity approved');
                    }
                    return true;
                };
            }),

            'items.machines.*' => Rule::forEach(function () {

                return function ($attribute, $value, $fail) {
                    if (empty($value['id'])) {
                        return $fail($attribute . '.id field is required.');
                    }
                    if (empty($value['allocation'])) {
                        return $fail($attribute . '.allocation field is required.');
                    }


                    $model = MaterialRequisitionItem::without(['customer', 'product'])
                        ->whereBelongsTo($this->route('material_requisition'), 'request')
                        ->find($value['id']);

                    if (empty($model)) {
                        return $fail($attribute . '.id item does not exists');
                    }

                    if ($model->approved_qty < count($value['allocation'])) {
                        return $fail($attribute . 'allocation exceeds quantity approved');
                    }

                    //validate the allocation items
                    foreach ($value['allocation'] as $index => $allotted) {

                        $subRules = [
                            'product_item_id' => ['required', 'exists:App\Models\ProductItem,id'],
                            'warrant_start' => 'nullable|date|required_unless:warrant_end,null',
                            'warrant_end' => 'nullable|date|required_unless:warrant_start,null',
                        ];

                        $x = $attribute . '.allocation.' . $index;

                        $subRulesMessages = [
                            'product_item_id.required' => ':attribute is not a valid date at ' . $x,
                            'product_item_id.exists' => ':attribute does not exists at ' . $x,
                            'warrant_start.date' => ':attribute is not a valid date at ' . $x,
                            'warrant_end.date' => ':attribute is not a valid date at ' . $x
                        ];

                        $validator = Validator::make($allotted, $subRules, $subRulesMessages);
                        if ($validator->fails()) {
                            return $fail($validator->errors()->first());
                        }
                    }
                    return true;
                };

            })
        ];
    }
}
