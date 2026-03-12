<?php

namespace App\Http\Controllers\Post;

use App\Actions\Post\PostCreateAction;
use App\Actions\Post\PostUpdateAction;
use App\Exceptions\PostException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['tags', 'category', 'user'])->get();

        Log::debug('Posts were listed');

        return PostResource::collection($posts);
    }


    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(Post $post, StorePostRequest $request, PostCreateAction $action)
    {
        $data = $request->validated();

        $createdPost = DB::transaction(fn() => $action->handle($data, $post));

        Log::debug('Post stored with id: ' . $post->id);

        return PostResource::make($createdPost);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $post = Post::with(['tags', 'category', 'user'])->findOrFail($id);

            Log::debug('Post was listed with id: ' . $post->id);

        } catch (Exception $e) {

            throw new PostException($e->getMessage(), 0, $e);
        }
        return PostResource::make($post);
    }


    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Post $post, UpdatePostRequest $request, PostUpdateAction $action)
    {
        $data = $request->validated();

        $updatedPost = DB::transaction(fn() => $action->handle($post, $data));

        Log::debug('Post updated with id: ' . $updatedPost->id);

        return PostResource::make($updatedPost);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        Log::debug('Post deleted with id: ' . $post->id);

        return response()->noContent();
    }
}
