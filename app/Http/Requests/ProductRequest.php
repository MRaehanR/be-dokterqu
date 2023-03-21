<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => 'required',
            'name' => 'required|min:5,max:100',
            'desc' => 'required|min:5',
            'slug' => 'required|unique:article_posts',
            'additional_info' => 'nullable',
            'images.*' => 'required|mimes:jpg,png,jpeg,bmp,webp|max:5000',
            'images' => 'max:5',
        ];
    }
}
