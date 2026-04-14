<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tag\CreateTagAction;
use App\Actions\Tag\UpdateTagAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\TagRequest;
use App\Http\Resources\Tag\TagResource;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class TagController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/tags',
        description: 'Returns a collection of tags including the count of associated posts.',
        summary: 'Get a list of all tags',
        security: [['bearerAuth' => []]],
        tags: ['Admin Tags'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/TagResource')
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index()
    {
        $tags = Tag::withCount('posts')->get();

        Log::debug('Tags found');

        return TagResource::collection($tags);
    }

    #[OAT\Post(
        path: '/api/admin/tags',
        description: 'Creates a new tag and returns the created resource.',
        summary: 'Create a new tag',
        security: [['bearerAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/TagRequest')
        ),
        tags: ['Admin Tags'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Tag created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/TagResource')
            ),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function store(TagRequest $request, CreateTagAction $action): TagResource
    {
        $tag = DB::transaction(fn () => $action->handle($request));

        return TagResource::make($tag);
    }

    #[OAT\Get(
        path: '/api/admin/tags/{id}',
        description: 'Returns a single tag resource by its ID.',
        summary: 'Get tag details',
        security: [['bearerAuth' => []]],
        tags: ['Admin Tags'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the tag to retrieve',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(ref: '#/components/schemas/TagResource')
            ),
            new OAT\Response(response: 404, description: 'Tag not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function show(Tag $tag): TagResource
    {
        Log::debug("Tag found with id: {$tag->id}");

        return TagResource::make($tag);
    }

    #[OAT\Put(
        path: '/api/admin/tags/{id}',
        description: 'Updates the tag details and returns the updated resource.',
        summary: 'Update an existing tag',
        security: [['bearerAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/TagRequest')
        ),
        tags: ['Admin Tags'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the tag to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Tag updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/TagResource')
            ),
            new OAT\Response(response: 404, description: 'Tag not found'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function update(TagRequest $request, Tag $tag, UpdateTagAction $action): TagResource
    {
        $tag = DB::transaction(fn () => $action->handle($request, $tag));

        return TagResource::make($tag);
    }

    #[OAT\Delete(
        path: '/api/admin/tags/{id}',
        description: 'Permanently removes a tag from the database by its ID.',
        summary: 'Delete a tag',
        security: [['bearerAuth' => []]],
        tags: ['Admin Tags'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the tag to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Tag deleted successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Tag deleted'),
                    ]
                )
            ),
            new OAT\Response(response: 404, description: 'Tag not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Tag $tag): Response
    {
        $tag->delete();

        Log::debug('Tag deleted with id: '.$tag->id);

        return response()->noContent();
    }
}
