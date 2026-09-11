<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin',   'email' => 'admin@konoha.com',   'password' => 'password', 'role' => 'admin'],
            ['name' => 'Manager', 'email' => 'manager@konoha.com', 'password' => 'password', 'role' => 'manager'],
            ['name' => 'Cashier', 'email' => 'cashier@konoha.com', 'password' => 'password', 'role' => 'cashier'],
        ];

        foreach ($users as $u) {
            if (!User::where('email', $u['email'])->exists()) {
                User::create([
                    'name' => $u['name'],
                    'gender' => 'male',
                    'email' => $u['email'],
                    'password' => Hash::make($u['password']),
                    'role' => $u['role'],
                ]);
            } else {
                User::where('email', $u['email'])->update(['role' => $u['role'], 'password' => Hash::make($u['password'])]);
            }
        }
    }
}