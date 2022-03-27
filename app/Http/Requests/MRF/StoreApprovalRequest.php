<?php

namespace App\Http\Requests\MRF;

use App\Models\MaterialRequisitionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreApprovalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('approve', $this->route('material_requisition'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'items' => 'array|required|min:1',
            'items.*' => [
                Rule::forEach(function () {
                    return function ($attribute, $value, $fail) {

                        if (empty($value['id'])) {
                            return $fail($attribute . '.id field is required.');
                        }
                        if (empty($value['approved_qty'])) {
                            return $fail($attribute . '.approved_qty field is required.');
                        }

                        $itemModel = MaterialRequisitionItem::without(['customer', 'product'])
                            ->find($value['id']);

                        if(empty($itemModel)){
                            return $fail($attribute . '.id item does not exists');
                        }

                        if ($itemModel->verified_qty < $value['approved_qty']) {
                            return $fail($attribute . '.approved_qty exceeds quantity verified');
                        }
                        return true;
                    };
                })
            ]
        ];
    }
}
