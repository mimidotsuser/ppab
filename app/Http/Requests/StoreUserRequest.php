<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|max:250',
            'last_name' => 'nullable|max:250',
            'email' => ['required', 'email', Rule::unique(User::class, 'email')],
            'role_id' => ['required', Rule::exists(Role::class, 'id')]
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'User account already exists',
            'role_id.required' => 'User role is required',
            'role_id.exists' => 'User role is invalid',
        ];
    }
}
