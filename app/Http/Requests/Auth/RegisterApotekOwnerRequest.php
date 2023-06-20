<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class RegisterApotekOwnerRequest extends Request
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
            'province_id' => 'required|exists:provinces,prov_id',
            'city_id' => 'required|exists:cities,city_id',
            'name' => 'required|string|min:5|max:50',
            'address' => 'required|string|min:5|max:100',
            'ktp' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
            'npwp' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
            'surat_izin_usaha' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:2048',
            'image.*' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:5000',
            'image' => 'max:5',
            'latitude' => 'required',
            'longitude' => 'required',
        ];
    }
}
