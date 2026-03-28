<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostByCategoryAction
{
    public function __construct()
    {
    }

    public function handle(int $categoryId, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with(['category', 'user', 'tags'])
            ->byCategory($categoryId)
            ->latest()
            ->paginate($perPage);
    }
}
