<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@konoha.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'gender' => 'male',
                'email' => 'admin@konoha.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]);
        }
    }
}