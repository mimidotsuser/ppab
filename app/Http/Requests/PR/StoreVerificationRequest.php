<?php

namespace App\Http\Requests\PR;

use App\Models\PurchaseRequestItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use JetBrains\PhpStorm\ArrayShape;

class StoreVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('verify', $this->route('purchase_request'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    #[ArrayShape(['items' => "string", 'items.*' => "\Illuminate\Validation\NestedRules"])]
    public function rules(): array
    {
        return [
            'items' => 'array|required|min:1',
            'items.*' => Rule::forEach(function () {
                return [
                    function ($attr, $value, $fail) {
                        $subValidator = Validator::make($value, [
                            'id' => 'bail|required|exists:App\Models\PurchaseRequestItem,id',
                            'verified_qty' => 'bail|required|integer|numeric|min:0'
                        ], ['id.exists' => ':attribute does not exists']);

                        if ($subValidator->fails()) {
                            return $fail($subValidator->errors()->first());
                        }

                        $itemModel = PurchaseRequestItem::without(['customer', 'product'])
                            ->findOrFail($value['id']);

                        if ($itemModel->requested_qty < $value['verified_qty']) {
                            return $fail($attr . '.verified_qty exceeds quantity requested ');
                        }
                        return true;
                    },
                ];

            })
        ];
    }
}
