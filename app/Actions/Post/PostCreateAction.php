<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Support\Str;

class PostCreateAction
{
    public function handle(array $data, Post $post): Post
    {
        $mappedData = $this->mappedData($post, $data);
        $mappedData->save();
        $tagIds = $data['tags'] ?? [];
        $mappedData->tags()->sync($tagIds);
        return $mappedData;
    }

    public function mappedData(Post $post, array $data): Post
    {
        $post->title = $data['title'];
        $post->content = $data['content'];
        $post->slug = Str::slug($data['slug']);
        $post->user_id = $data['user_id'] ?? $post->user_id;
        $post->is_published = $data['is_published'] ?? false;
        $post->category_id = $data['category_id'] ?? $post->category_id;
        return $post;
    }
}
