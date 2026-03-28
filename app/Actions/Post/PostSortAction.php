<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostSortAction
{
    public function __construct()
    {
    }

    public function handle(): Collection
    {
        return Post::query()
            ->with(['category', 'user', 'tags'])
            ->recent()
            ->get();
    }
}
