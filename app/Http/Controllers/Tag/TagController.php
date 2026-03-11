<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tag\CreateTagAction;
use App\Actions\Tag\UpdateTagAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\TagRequest;
use App\Http\Resources\Tag\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')->get();

        Log::debug('Tags found');

        return TagResource::collection($tags);
    }


    public function store(TagRequest $request, CreateTagAction $action): TagResource
    {
        $tag = $action->handle($request);

        return TagResource::make($tag);
    }


    public function show(Tag $tag): TagResource
    {
        Log::debug('Tag found with id: {$tag->id}');

        return TagResource::make($tag);
    }


    public function update(TagRequest $request, Tag $tag, UpdateTagAction $action): TagResource
    {
        $action->handle($request, $tag);

        return TagResource::make($tag);
    }


    public function destroy(Tag $tag): JsonResponse
    {
        $tag->delete();

        Log::debug('Tag deleted with id: ' . $tag->id);

        return response()->json(['message' => 'Tag deleted'], 204);
    }
}
