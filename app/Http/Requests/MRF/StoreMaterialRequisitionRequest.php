<?php

namespace App\Http\Requests\MRF;

use App\Models\MaterialRequisition;
use App\Utils\MRFUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreMaterialRequisitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('create', MaterialRequisition::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:App\Models\Warehouse,id',
            'remarks' => 'nullable|max:255',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:App\Models\Product,id',
            'items.*.customer_id' => 'required|exists:App\Models\Customer,id',
            'items.*.worksheet_id' => 'nullable|exists:App\Models\Worksheet,id',
            'items.*.purpose_code' => ['required', Rule::in(array_keys(MRFUtils::purpose()))],
            'items.*.requested_qty' => 'required|numeric|min:1',
        ];
    }
}
