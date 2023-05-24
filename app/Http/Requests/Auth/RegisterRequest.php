<?php

namespace App\Http\Requests\Auth;

use App\Http\Traits\ValidationErrorMessageTrait;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    use ValidationErrorMessageTrait;

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
            'role' => 'required',
        ];

        if ($this->role == 1) {
            $rules = array_merge($rules, (new RegisterDoctorRequest())->rules());
            // dd($rules);
        } else if ($this->role == 2) {
            $rules = array_merge($rules, (new RegisterApotekOwnerRequest())->rules());
        }

        // dd(array($rules));

        return $rules;
    }
}
