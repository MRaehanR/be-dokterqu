<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Validation\Rule;

class RegisterRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|min:5',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'photo' => 'mimes:jpg,png,jpeg,bmp|max:2048',
            'phone' => 'required|unique:users|max:15',
            'gender' => 'required|in:m,f',
            'role' => ["required", Rule::in([User::TYPE_DOCTOR, USER::TYPE_APOTEK_OWNER, USER::TYPE_CUSTOMER])],
        ];

        if ($this->role == User::TYPE_DOCTOR) {
            $rules = array_merge($rules, (new RegisterDoctorRequest())->rules());
        } else if ($this->role == User::TYPE_APOTEK_OWNER) {
            $rules = array_merge($rules, (new RegisterApotekOwnerRequest())->rules());
        }

        return $rules;
    }
}
