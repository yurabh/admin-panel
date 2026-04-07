<?php

namespace App\Actions\Tag;

use App\Http\Requests\Tag\TagRequest;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class CreateTagAction
{
    public function handle(TagRequest $request): Tag
    {
        $tag = $request->validated();

        $createdTag = Tag::create($tag);

        if ($request->has('posts')) {
            $createdTag->posts()->sync($request->posts);
        }

        Log::debug('Tag created');

        return $createdTag;
    }
}
