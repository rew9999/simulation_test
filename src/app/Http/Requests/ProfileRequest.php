<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'postal_code' => 'required|string',
            'address' => 'required|string',
            'profile_image' => 'nullable|image',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ユーザー名を入力してください',
            'postal_code.required' => '郵便番号を入力してください',
            'address.required' => '住所を入力してください',
            'profile_image.image' => '画像ファイルを選択してください',
        ];
    }
}
