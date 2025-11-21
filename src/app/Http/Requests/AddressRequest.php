<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'postal_code' => 'required|string',
            'address' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required' => '郵便番号を入力してください',
            'address.required' => '住所を入力してください',
        ];
    }
}
