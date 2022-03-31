<?php

namespace App\Http\Requests\PR;

use App\Models\PurchaseRequestItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
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
        return false;
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
            'items.*' => Rule::forEach(function () {
                return [
                    function ($attr, $value, $fail) {
                        $subValidator = Validator::make($value, [
                            'id' => 'bail|required|exists:App\Models\PurchaseRequestItem,id',
                            'approved_qty' => 'bail|required|integer|numeric|min:0'
                        ], ['id.exists' => ':attribute does not exists']);

                        if ($subValidator->fails()) {
                            return $fail($subValidator->errors()->first());
                        }

                        $itemModel = PurchaseRequestItem::without(['customer', 'product'])
                            ->findOrFail($value['id']);

                        if ($itemModel->verified_qty < $value['approved_qty']) {
                            return $fail($attr . '.approved_qty exceeds verified quantity');
                        }
                        return true;
                    },
                ];

            })
        ];

    }
}
