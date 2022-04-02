<?php

namespace App\Http\Requests;

use App\Models\Currency;
use App\Models\Product;
use App\Models\RequestForQuotation;
use App\Models\RequestForQuotationItem;
use App\Models\UnitOfMeasure;
use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::user()->can('update', $this->route('purchase_order'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'rfq_id' => ['nullable', Rule::exists(RequestForQuotation::class, 'id')],
            'doc_validity' => 'required|date',
            'vendor_id' => ['required', Rule::exists(Vendor::class, 'id')],
            'currency_id' => ['required', Rule::exists(Currency::class, 'id')],
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.rfq_item_id' => ['nullable',
                Rule::exists(RequestForQuotationItem::class, 'id')],
            'items.*.qty' => 'required|numeric|integer|min:1',
            'items.*.unit_price' => 'numeric|integer|min:1',
            'items.*.unit_of_measure_id' => ['required',
                Rule::exists(UnitOfMeasure::class, 'id')]
        ];
    }
}
