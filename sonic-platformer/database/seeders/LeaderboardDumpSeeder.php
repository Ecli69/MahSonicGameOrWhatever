<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LeaderboardDumpSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['login' => 'Sonic???', 'best_time' => 12.45, 'total_rings' => 50, 'attempts' => 5, 'avatar' => 'icon2.png', 'color' => '#0000FF'],
            ['login' => 'Tails', 'best_time' => 14.20, 'total_rings' => 12, 'attempts' => 3, 'avatar' => 'icon4.png', 'color' => '#FFA500'],
            ['login' => 'Knuckles', 'best_time' => 15.10, 'total_rings' => 18, 'attempts' => 8, 'avatar' => 'icon5.png', 'color' => '#FF0000'],
            ['login' => 'Amy', 'best_time' => 16.50, 'total_rings' => 10, 'attempts' => 2, 'avatar' => 'icon3.png', 'color' => '#FF69B4'],
            ['login' => 'Shadow', 'best_time' => 12.45, 'total_rings' => 50, 'attempts' => 6, 'avatar' => 'icon8.png', 'color' => '#800080'],
            ['login' => 'Rouge', 'best_time' => 18.30, 'total_rings' => 8, 'attempts' => 1, 'avatar' => 'icon6.png', 'color' => '#FF00FF'],
            ['login' => 'Cream', 'best_time' => 20.00, 'total_rings' => 5, 'attempts' => 4, 'avatar' => 'icon7.png', 'color' => '#FFC0CB'],
            ['login' => 'Blaze', 'best_time' => 13.90, 'total_rings' => 14, 'attempts' => 6, 'avatar' => 'icon1.png', 'color' => '#8A2BE2'],
        ];

        foreach ($users as $u) {
            User::create([
                'login' => $u['login'],
                'password' => Hash::make('123456'), // Пароль для всех: 123456
                'best_time' => $u['best_time'],
                'total_rings' => $u['total_rings'],
                'attempts' => $u['attempts'],
                'avatar' => $u['avatar'],
                'color' => $u['color'],
            ]);
        }
    }
}