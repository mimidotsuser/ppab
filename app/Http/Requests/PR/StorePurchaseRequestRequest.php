<?php

namespace App\Http\Requests\PR;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePurchaseRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', PurchaseRequest::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'remarks' => ['nullable', 'max:250'],
            'warehouse_id' => ['required', 'exists:App\Models\Warehouse,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:App\Models\Product,id'],
            'items.*.requested_qty' => ['required', 'numeric', 'integer', 'min:0']
        ];
    }
}
