<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class PostByCategoryIdAndDateFilterAction
{
    public function handle(array $filters): LengthAwarePaginator
    {
        return Post::query()
            ->with(['category', 'user'])
            ->filter($filters)
            ->latest('published_at')
            ->paginate(10);
    }
}
