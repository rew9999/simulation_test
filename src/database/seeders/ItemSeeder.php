<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $items = [
            [
                'name' => '腕時計',
                'price' => 15000,
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image' => 'images/Armani Mens Clock.jpg',
                'condition' => '良好',
                'categories' => ['ファッション', 'メンズ', 'アクセサリー'],
            ],
            [
                'name' => 'HDD',
                'price' => 5000,
                'description' => '高速で信頼性の高いハードディスク',
                'image' => 'images/HDD Hard Disk.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => ['家電'],
            ],
            [
                'name' => '玉ねぎ3束',
                'price' => 300,
                'description' => '新鮮な玉ねぎ3束のセット',
                'image' => 'images/iLoveIMG d.jpg',
                'condition' => 'やや傷や汚れあり',
                'categories' => ['キッチン'],
            ],
            [
                'name' => '革靴',
                'price' => 4000,
                'description' => 'クラシックなデザインの革靴',
                'image' => 'images/Leather Shoes Product Photo.jpg',
                'condition' => '状態が悪い',
                'categories' => ['ファッション', 'メンズ'],
            ],
            [
                'name' => 'ノートPC',
                'price' => 45000,
                'description' => '高性能なノートパソコン',
                'image' => 'images/Living Room Laptop.jpg',
                'condition' => '良好',
                'categories' => ['家電'],
            ],
            [
                'name' => 'マイク',
                'price' => 8000,
                'description' => '高音質カラオケマイク',
                'image' => 'images/Music Mic.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => ['家電'],
            ],
            [
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'description' => 'おしゃれなショルダーバッグ',
                'image' => 'images/Purse Fashion Pocket.jpg',
                'condition' => 'やや傷や汚れあり',
                'categories' => ['ファッション', 'レディース'],
            ],
            [
                'name' => 'タンブラー',
                'price' => 500,
                'description' => '使いやすいタンブラー',
                'image' => 'images/Tumbler Souvenir.jpg',
                'condition' => '状態が悪い',
                'categories' => ['キッチン'],
            ],
            [
                'name' => 'コーヒーミル',
                'price' => 4000,
                'description' => '手動のコーヒーミル',
                'image' => 'images/Waitress+with+Coffee+Grinder.jpg',
                'condition' => '良好',
                'categories' => ['キッチン'],
            ],
            [
                'name' => 'メイクセット',
                'price' => 2500,
                'description' => '便利なメイクアップセット',
                'image' => 'images/外出メイクアップセット.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => ['コスメ'],
            ],
        ];

        foreach ($items as $itemData) {
            $categories = $itemData['categories'];
            unset($itemData['categories']);

            $itemData['user_id'] = $user->id;
            $item = Item::create($itemData);

            $categoryIds = Category::whereIn('name', $categories)->pluck('id');
            $item->categories()->attach($categoryIds);
        }
    }
}
