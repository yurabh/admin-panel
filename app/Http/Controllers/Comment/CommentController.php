<?php

namespace App\Http\Controllers\Comment;

use App\Actions\Comment\CreateCommentAction;
use App\Actions\Comment\UpdateCommentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;
use Throwable;

class CommentController extends Controller
{
    use AuthorizesRequests;

    #[OAT\Get(
        path: '/api/comments',
        summary: 'Get list of all comments',
        tags: ['Comments'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful retrieval of comment list',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/CommentResource')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden'
            ),
        ]
    )]
    public function index()
    {
        $comments = Comment::with(['user', 'post'])->get();

        return CommentResource::collection($comments);
    }

    #[OAT\Post(
        path: '/api/comments',
        summary: 'Create a new comment',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CommentRequest')
        ),
        tags: ['Comments'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Comment created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/CommentResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation errors'
            ),
        ]
    )]
    public function store(CommentRequest $request, CreateCommentAction $action)
    {
        $comment = DB::transaction(fn () => $action->handle($request));

        return CommentResource::make($comment);
    }

    #[OAT\Get(
        path: '/api/comments/{id}',
        summary: 'Get comment by ID',
        tags: ['Comments'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the comment to retrieve',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful retrieval of the comment',
                content: new OAT\JsonContent(ref: '#/components/schemas/CommentResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 404,
                description: 'Comment not found'
            ),
        ]
    )]
    public function show(Comment $comment)
    {
        $comment->load(['user', 'post']);

        Log::debug('Comment found with id: '.$comment->id);

        return CommentResource::make($comment);
    }

    #[OAT\Put(
        path: '/api/comments/{id}',
        summary: 'Update an existing comment',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CommentRequest')
        ),
        tags: ['Comments'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the comment to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Comment updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/CommentResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden - You do not have permission to update this comment'
            ),
            new OAT\Response(
                response: 404,
                description: 'Comment not found'
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation errors'
            ),
        ]
    )]
    /**
     * @throws Throwable
     */
    public function update(CommentRequest $request, Comment $comment, UpdateCommentAction $action)
    {
        $this->authorize('update', $comment);

        $comment = DB::transaction(fn () => $action->handle($request, $comment));

        return CommentResource::make($comment);
    }

    #[OAT\Delete(
        path: '/api/comments/{id}',
        summary: 'Delete a comment',
        tags: ['Comments'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the comment to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Comment deleted successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Successfully deleted Comment'),
                    ],
                    type: 'object'
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden - You do not have permission to delete this comment'
            ),
            new OAT\Response(
                response: 404,
                description: 'Comment not found'
            ),
        ]
    )]
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        $this->authorize('delete', $comment);

        Log::debug('Comment found with id: '.$comment->id);

        $comment->delete();

        Log::debug('Comment removed with id: '.$comment->id);

        return response()->json([
            'message' => 'Successfully deleted Comment',
        ]);
    }
}
