<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
            'full_name' => "Muhamad Fauzaan",
            'username' => 'admin',
            'password' => 'admin123',
            'bio' => "Tak dung tak",
            'is_private' => false,
            ],
            [
            'full_name' => "Member",
            'username' => 'member',
            'password' => 'member123',
            'bio' => "Tak dung tak",
            'is_private' => false,
            ],
        ];

        foreach($users as $user) {
            User::create($user);
        }
    }
}
