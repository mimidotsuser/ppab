<?php

namespace App\Http\Requests\MRF;

use App\Models\MaterialRequisitionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('verify', $this->route('material_requisition'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remarks' => 'nullable|max:250',
            'items' => 'array|required|min:1',
            'items.*' => Rule::forEach(function () {
                return [
                    'id' => 'bail|required|exists:App\Models\MaterialRequisitionItem,id',
                    'verified_qty' => 'bail|required|integer|numeric|min:0',
                    '.' => function ($attribute, $value, $fail) {
                        if (empty($value['id'])) {
                            return $fail($attribute . '.id field is required.');
                        }
                        if (is_numeric($value['verified_qty'])) {
                            return $fail($attribute . '.verified_qty field is required.');
                        }
                        $itemModel = MaterialRequisitionItem::without(['customer', 'product'])
                            ->findOrFail($value['id']);

                        if ($itemModel->requested_qty < $value['verified_qty']) {
                            return $fail($attribute . '.verified_qty exceeds quantity requested');
                        }
                        return true;
                    }];

            })
        ];
    }
}
