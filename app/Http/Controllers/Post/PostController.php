<?php

namespace App\Http\Controllers\Post;

use App\Actions\Post\PostByCategoryIdAndDateFilterAction;
use App\Actions\Post\PostByCategoryAction;
use App\Actions\Post\PostCreateAction;
use App\Actions\Post\PostSearchAction;
use App\Actions\Post\PostSortAction;
use App\Actions\Post\PostUpdateAction;
use App\Exceptions\PostException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class PostController extends Controller
{
    use AuthorizesRequests;

    #[OAT\Get(
        path: '/api/admin/posts',
        description: 'Returns a collection of posts with related tags, categories, and users.',
        summary: 'Get a list of all posts',
        tags: ['Admin Posts'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/PostResource')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden'
            )
        ]
    )]
    public function index()
    {
        $posts = Post::with(['tags', 'category', 'user'])->get();

        Log::debug('Posts were listed');

        return PostResource::collection($posts);
    }


    #[OAT\Post(
        path: '/api/admin/posts',
        description: 'Creates a new post record and returns the created resource.',
        summary: 'Create a new post',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/StorePostRequest')
        ),
        tags: ['Admin Posts'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Post created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/PostResource')
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
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


    #[OAT\Get(
        path: '/api/admin/posts/{id}',
        description: 'Returns a single post with related tags, category, and user details.',
        summary: 'Get a specific post by ID',
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the post to return',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(ref: '#/components/schemas/PostResource')
            ),
            new OAT\Response(
                response: 404,
                description: 'Post not found'
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
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


    #[OAT\Put(
        path: '/api/admin/posts/{id}',
        description: 'Updates a post record and returns the updated resource.',
        summary: 'Update an existing post',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/UpdatePostRequest')
        ),
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the post to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Post updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/PostResource')
            ),
            new OAT\Response(response: 403, description: 'Forbidden / Unauthorized'),
            new OAT\Response(response: 404, description: 'Post not found'),
            new OAT\Response(response: 422, description: 'Validation error')
        ]
    )]
    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Post $post, UpdatePostRequest $request, PostUpdateAction $action)
    {
        $this->authorize('update', $post);

        $data = $request->validated();

        $updatedPost = DB::transaction(fn() => $action->handle($post, $data));

        Log::debug('Post updated with id: ' . $updatedPost->id);

        return PostResource::make($updatedPost);
    }


    #[OAT\Delete(
        path: '/api/admin/posts/{id}',
        description: 'Deletes a specific post record from the database.',
        summary: 'Delete a post',
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the post to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Post deleted successfully (No Content)'
            ),
            new OAT\Response(
                response: 404,
                description: 'Post not found'
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden'
            )
        ]
    )]
    public function destroy(Post $post)
    {
        $post->delete();

        Log::debug('Post deleted with id: ' . $post->id);

        return response()->noContent();
    }


    #[OAT\Get(
        path: '/api/admin/posts/search',
        description: 'Search posts by title or content using a term.',
        summary: 'Search posts',
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(
                name: 'term',
                description: 'The search term',
                in: 'query',
                required: true,
                schema: new OAT\Schema(type: 'string', minLength: 2)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Found posts',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/PostResource')
                )
            )
        ]
    )]
    public function search(Request $request, PostSearchAction $action)
    {
        $term = $request->query('term');

        if (empty($term)) {
            return response()->json(['message' => 'Search term is required'], 400);
        }

        $posts = $action->handle(['search' => $term]);

        Log::debug("User searched for: {$term}");

        return PostResource::collection($posts);
    }


    #[OAT\Get(
        path: '/api/admin/posts/sorted-by-date',
        description: 'Get the most recent published posts.',
        summary: 'Get recent posts',
        tags: ['Admin Posts'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'List of recent posts',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/PostResource')
                )
            )
        ]
    )]
    public function getSortedByDatePublishedAt(PostSortAction $action)
    {
        $posts = $action->handle();

        Log::debug('Filtered posts by date is done');

        return PostResource::collection($posts);
    }


    #[OAT\Get(
        path: '/api/admin/posts/category/{categoryId}',
        summary: 'Get posts by category with pagination',
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(
                name: 'categoryId',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer')
            ),
            new OAT\Parameter(
                name: 'page',
                description: 'Page number',
                in: 'query',
                schema: new OAT\Schema(type: 'integer', default: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Paginated list of posts',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'data', type: 'array', items: new OAT\Items(ref: '#/components/schemas/PostResource')),
                        new OAT\Property(property: 'meta', type: 'object'),
                        new OAT\Property(property: 'links', type: 'object')
                    ]
                )
            )
        ]
    )]
    public function getByCategoryId(int $categoryId, PostByCategoryAction $action)
    {
        $posts = $action->handle($categoryId);

        Log::debug("Posts listed for category ID: {$categoryId}");

        return PostResource::collection($posts);
    }


    #[OAT\Get(
        path: '/api/admin/posts/filter',
        summary: 'Count posts for category',
        tags: ['Admin Posts'],
        parameters: [
            new OAT\Parameter(name: 'category_id', in: 'query', schema: new OAT\Schema(type: 'integer')),
            new OAT\Parameter(name: 'is_published', in: 'query', schema: new OAT\Schema(type: 'boolean')),
            new OAT\Parameter(name: 'date_from', in: 'query', schema: new OAT\Schema(type: 'string', format: 'date')),
            new OAT\Parameter(name: 'date_to', in: 'query', schema: new OAT\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OAT\Response(response: 200, description: 'Paginated posts', content: new OAT\JsonContent(type: 'object'))
        ]
    )]
    public function filter(Request $request, PostByCategoryIdAndDateFilterAction $action)
    {
        $filters = $request->only(['category_id', 'is_published', 'date_from', 'date_to']);

        $posts = $action->handle($filters);

        return PostResource::collection($posts);
    }
}
