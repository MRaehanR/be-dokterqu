<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class RegisterDoctorRequest extends Request
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
        return [
            'type_doctor_id' => 'required',
            'experience' => 'required',
            'alumnus' => 'required',
            'alumnus_tahun' => 'required|integer',
            'tempat_praktik' => 'required',
            'cv' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
            'str' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
            'ktp' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
        ];
    }
}
