<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Contest;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Тестовый пользователь',
            'email' => 'participant@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PARTICIPANT,
        ]);

        User::create([
            'name' => 'Жюри',
            'email' => 'jury@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_JURY,
        ]);

        User::create([
            'name' => 'Админ',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        Contest::create([
            'title' => 'Тестовый конкурс',
            'description' => 'Это тестовый конкурс',
            'deadline_at' => now()->addDays(30),
            'is_active' => true,
        ]);
    }
}