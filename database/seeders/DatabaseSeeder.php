<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
User::create([
    'name' => '管理者',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);
    }
}
