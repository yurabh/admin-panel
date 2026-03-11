<?php

namespace App\Actions\Comment;

use App\Http\Requests\Comment\CommentRequest;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CreateCommentAction
{

    public function __construct()
    {
    }

    public function handle(CommentRequest $request): Comment|JsonResponse
    {
        $request->validated();

        Log::debug('Validation passed successfully');

        $comment = Comment::create([
            'post_id' => $request["post_id"],
            'user_id' => $request["user_id"],
            'content' => $request["content"],
            'is_approved' => $request["is_approved"],
        ]);

        Log::debug('Comment created with id: ', [$comment->id]);

        return $comment->load(['user', 'post']);
    }
}
