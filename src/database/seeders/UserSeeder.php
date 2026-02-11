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
    }
}
