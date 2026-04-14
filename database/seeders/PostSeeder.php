<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $tags = Tag::all();

        foreach (range(1, 20) as $i) {

            $title = "Test Post {$i}";

            $post = Post::query()->create([
                'user_id' => $users->random()->id,
                'title' => $title,
                'slug' => Str::slug($title).'-'.uniqid(),
                'content' => "This is the content of post {$i}",
                'is_published' => rand(0, 1),
                'published_at' => now(),
                'category_id' => $categories->random()->id,
            ]);

            $post->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')->toArray()
            );
        }
    }
}
