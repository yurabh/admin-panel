<?php

namespace App\Actions\Comment;

use App\Events\NewCommentEvent;
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
        $data = $request->validated();

        Log::debug('Validation passed successfully');

        $comment = Comment::create($data);

        Log::debug('Comment created with id: ', [$comment->id]);

        event(new NewCommentEvent($comment));

        return $comment->load(['user', 'post']);
    }
}
