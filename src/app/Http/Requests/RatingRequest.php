<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください',
            'rating.min' => '評価は1以上で選択してください',
            'rating.max' => '評価は5以下で選択してください',
        ];
    }
}
