<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        User::create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '150-0043',
            'address' => '東京都渋谷区道玄坂2-10-12',
            'building' => '新大宗ビル3号館',
        ]);

        User::create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '160-0022',
            'address' => '東京都新宿区新宿3-1-1',
            'building' => null,
        ]);

        User::create([
            'name' => '鈴木一郎',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '100-0005',
            'address' => '東京都千代田区丸の内1-1-1',
            'building' => '丸の内タワー',
        ]);

        User::create([
            'name' => '田中美咲',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '170-0013',
            'address' => '東京都豊島区東池袋1-2-3',
            'building' => 'サンシャインビル',
        ]);

        User::create([
            'name' => '高橋健太',
            'email' => 'takahashi@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '220-0012',
            'address' => '神奈川県横浜市西区みなとみらい2-2-1',
            'building' => 'ランドマークタワー',
        ]);

        User::create([
            'name' => '伊藤愛',
            'email' => 'ito@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '530-0001',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => null,
        ]);

        User::create([
            'name' => '渡辺誠',
            'email' => 'watanabe@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '810-0001',
            'address' => '福岡県福岡市中央区天神1-1-1',
            'building' => '天神ビル5F',
        ]);

        User::create([
            'name' => '中村さくら',
            'email' => 'nakamura@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '600-8216',
            'address' => '京都府京都市下京区東塩小路町',
            'building' => '京都駅前ビル',
        ]);

        User::create([
            'name' => '小林優',
            'email' => 'kobayashi@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code' => '460-0008',
            'address' => '愛知県名古屋市中区栄1-1-1',
            'building' => null,
        ]);
    }
}
