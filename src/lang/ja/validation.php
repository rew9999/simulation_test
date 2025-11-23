<?php

return [
    'required' => ':attributeを入力してください',
    'email' => ':attributeはメール形式で入力してください',
    'string' => ':attributeは文字列である必要があります',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],
    'unique' => 'この:attributeは既に使用されています',
    'confirmed' => ':attributeと一致しません',
    'integer' => ':attributeは数値で入力してください',
    'image' => ':attributeは画像ファイルである必要があります',
    'array' => ':attributeは配列である必要があります',

    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => 'お名前',
        'postal_code' => '郵便番号',
        'address' => '住所',
        'building' => '建物名',
        'profile_image' => 'プロフィール画像',
        'payment_method' => '支払い方法',
        'content' => 'コメント',
        'description' => '商品の説明',
        'price' => '販売価格',
        'condition' => '商品の状態',
        'image' => '商品画像',
        'categories' => 'カテゴリ',
    ],
];
