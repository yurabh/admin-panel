<?php

namespace App\Http\Controllers\Category;

use App\Actions\Category\CategoryCreateAction;
use App\Actions\Category\CategoryUpdateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Log;
use OpenApi\Attributes as OAT;

class CategoryController extends Controller
{
    #[OAT\Get(
        path: '/api/admin/categories',
        description: 'Retrieves all categories along with their associated posts.',
        summary: 'Get list of all categories',
        tags: ['Categories'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful retrieval of category list',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/CategoryResource')
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function index()
    {
        $categories = Category::with(['posts'])->get();

        Log::debug('Categories were listed');

        return CategoryResource::collection($categories);
    }

    #[OAT\Post(
        path: '/api/admin/categories',
        description: 'Creates a new category using the provided name and slug. Executed within a database transaction.',
        summary: 'Create a new category',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CategoryRequest')
        ),
        tags: ['Categories'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 403,
                description: 'Forbidden'
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation errors'
            ),
        ]
    )]
    /**
     * Store a newly created resource in storage.
     *
     * @throws \Throwable
     */
    public function store(CategoryRequest $request, CategoryCreateAction $action)
    {
        $category = DB::transaction(fn () => $action->handle($request));

        return CategoryResource::make($category);
    }

    #[OAT\Get(
        path: '/api/admin/categories/{id}',
        description: 'Retrieves a single category with its associated posts by ID.',
        summary: 'Get category by ID',
        tags: ['Categories'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the category to retrieve',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful retrieval of the category',
                content: new OAT\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not found'
            ),
        ]
    )]
    public function show(Category $category)
    {
        $category->load(['posts']);

        Log::debug('Category found with id: '.$category->id);

        return CategoryResource::make($category);
    }

    #[OAT\Put(
        path: '/api/admin/categories/{id}',
        description: 'Updates category name and slug within a database transaction.',
        summary: 'Update an existing category',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/CategoryRequest')
        ),
        tags: ['Categories'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the category to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Category updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/CategoryResource')
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not found'
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation errors'
            ),
        ]
    )]
    /**
     * Update the specified resource in storage.
     *
     * @throws \Throwable
     */
    public function update(Category $category, CategoryRequest $request, CategoryUpdateAction $action)
    {
        $category = DB::transaction(fn () => $action->handle($request, $category));

        return CategoryResource::make($category);
    }

    #[OAT\Delete(
        path: '/api/admin/categories/{id}',
        description: 'Deletes a category only if it has no associated posts.',
        summary: 'Delete a category',
        tags: ['Categories'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'The ID of the category to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Category deleted successfully or deletion blocked by existing posts',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Category deleted with id: 1'),
                    ],
                    type: 'object'
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
            new OAT\Response(
                response: 404,
                description: 'Category not found'
            ),
        ]
    )]
    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return response()->json(['message' => 'Could not delete Category because posts exist for this category']);
        }

        $category->delete();

        Log::debug('Category deleted with id: '.$category->id);

        return response()->json(['message' => 'Category deleted with id: '.$category->id]);
    }
}
