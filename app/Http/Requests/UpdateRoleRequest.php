<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', $this->route('role'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'min:2', 'max:255',
                Rule::unique('App\Models\Role', 'name')
                    ->ignore($this->route('role'))],
            'description' => 'sometimes|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*.id' => 'exists:App\Models\Permission,id'
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Role with similar name already exist',
            'permissions.*.id.exists' => 'Invalid permissions provided'
        ];
    }
}
