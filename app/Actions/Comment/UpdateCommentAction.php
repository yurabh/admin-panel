<?php

namespace App\Actions\Comment;

use App\Http\Requests\Comment\CommentRequest;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCommentAction
{
    /**
     * @throws \Throwable
     */
    public function handle(CommentRequest $request, Comment $comment): Comment
    {
        $data = $request->validated();

        Log::debug('Validation passed successfully');

        $comment->update($data);

        Log::debug('Comment updated with id: ' . $comment->id);

        return $comment;
    }
}
