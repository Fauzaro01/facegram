<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;

class Posts extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $id = User::where('username', 'admin')->first()->id;
        Post::create([
            'caption'=> "hehehe",
            'user_id' => $id,
        ]);
    }
}
