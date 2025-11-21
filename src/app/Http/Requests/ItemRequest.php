<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'condition' => 'required|string',
            'image' => 'required|image',
            'categories' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '商品名を入力してください',
            'description.required' => '商品の説明を入力してください',
            'price.required' => '販売価格を入力してください',
            'price.integer' => '販売価格は数値で入力してください',
            'price.min' => '販売価格は0以上で入力してください',
            'condition.required' => '商品の状態を選択してください',
            'image.required' => '商品画像を選択してください',
            'image.image' => '画像ファイルを選択してください',
            'categories.required' => 'カテゴリを選択してください',
        ];
    }
}
