<?php

namespace App\Actions\Post;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

class PostSearchAction
{
    public function __construct()
    {
    }

    public function handle(array $filters): Collection
    {
        return Post::query()
            ->with(['tags', 'category', 'user'])
            ->search($filters['search'] ?? null)
            ->latest()
            ->get();
    }
}
