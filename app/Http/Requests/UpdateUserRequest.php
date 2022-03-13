<?php

namespace App\Http\Requests;

use App\Utils\UserUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'sometimes|max:250',
            'last_name' => 'sometimes|max:250',
            'email' => ['sometimes', 'email',
                Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role_id' => 'sometimes|exists:roles,id',
            'status' => ['sometimes', Rule::in([UserUtils::Suspended, UserUtils::Active]),
                Rule::when($this->route('user') //prevent updating status of invited user
                        ->status === UserUtils::PendingActivation, 'prohibited')]
        ];
    }

    public function messages()
    {
        return [
            'status.in' => 'You can only suspend or set user\'s account as active',
            'status.prohibited' => 'Cannot update user status until they have accepted the invite'
        ];
    }
}
