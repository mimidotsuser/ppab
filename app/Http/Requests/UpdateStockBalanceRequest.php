<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateStockBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('stock_balance'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'total_qty_in' => 'sometimes|numeric|min:0',
            'total_qty_out' => 'sometimes|numeric|min:0',
            'issue_requests_total' => 'sometimes|numeric|min:0',
            'reorder_requests_total' => 'sometimes|numeric|min:0',
        ];
    }
}
