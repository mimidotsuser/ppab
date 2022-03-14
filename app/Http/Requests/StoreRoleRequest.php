<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', Role::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:2|unique:roles,name|max:200',
            'description' => 'sometimes|max:200',
            'permissions' => 'required|array',
            'permissions.*.id' => 'exists:permissions,id'
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
