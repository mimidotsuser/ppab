<?php

namespace App\Http\Requests;

use App\Models\MaterialRequisition;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreStandbySpareCheckinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return \Illuminate\Auth\Access\Response
     */
    public function authorize()
    {
        return Gate::allowIf(fn($user) => $user->role->permissions->contains('name', 'standByCheckIn.create'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'material_request_id' => ['required',
                Rule::exists(MaterialRequisition::class, 'id')],
            'items' => 'array|required|min:1',
            'items.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'items.*.qty' => 'required|min:1|integer|numeric',
            'remarks' => 'nullable|string|max:250'
        ];
    }
}
