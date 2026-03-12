<?php

namespace App\Http\Controllers\Comment;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\UpdateCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Throwable;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::with(['user', 'post'])->get();

        return CommentResource::collection($comments);
    }


    public function store(CommentRequest $request, CreateCommentAction $action)
    {
        $comment = $action->handle($request);

        return CommentResource::make($comment);
    }


    public function show(Comment $comment)
    {
        $comment->load(['user', 'post']);

        Log::debug('Comment found with id: ' . $comment->id);

        return CommentResource::make($comment);
    }


    /**
     * @throws Throwable
     */
    public function update(CommentRequest $request, Comment $comment, UpdateCommentAction $action)
    {
        $this->authorize('update', $comment);

        $comment = $action->handle($request, $comment);

        return CommentResource::make($comment);
    }


    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        $this->authorize('delete', $comment);

        Log::debug('Comment found with id: ' . $comment->id);

        $comment->delete();

        Log::debug('Comment removed with id: ' . $comment->id);

        return response()->json([
            'message' => 'Successfully deleted Comment'
        ]);
    }
}
