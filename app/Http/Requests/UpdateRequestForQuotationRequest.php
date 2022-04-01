<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\UnitOfMeasure;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRequestForQuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', $this->route('request_for_quotation'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'purchase_request_id' => ['required',
                Rule::exists(PurchaseRequest::class, 'id')],
            'download' => 'nullable|boolean',
            'closing_date' => 'required|date',
            'vendors' => 'required|array|min:1',
            'vendors.*.id' => ['required', Rule::exists(Vendor::class, 'id')],
            'items' => 'required|array|min:1',
            'items.*.purchase_request_item_id' => ['nullable',
                Rule::exists(PurchaseRequestItem::class, 'id')],
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.qty' => 'required|numeric|integer|min:0',
            'items.*.unit_of_measure_id' => ['required',
                Rule::exists(UnitOfMeasure::class, 'id')]
        ];
    }
}
