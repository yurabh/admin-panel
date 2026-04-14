<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();

        Comment::factory(5)->create([
            'user_id' => fn () => $users->random()->id,
            'post_id' => fn () => $posts->random()->id,
        ]);
    }
}
