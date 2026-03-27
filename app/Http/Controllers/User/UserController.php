<?php

namespace App\Http\Controllers\User;

use App\Actions\Auth\RegistrationAction;
use App\Actions\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class UserController extends Controller
{
    use AuthorizesRequests;

    #[OAT\Get(
        path: '/api/users',
        description: 'Returns a collection of users with their associated posts, comments, and pages.',
        summary: 'Get a list of all users',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(ref: '#/components/schemas/UserResource')
                )
            ),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 403, description: 'Forbidden')
        ]
    )]
    public function index()
    {
        return UserResource::collection(User::with(['posts', 'comments', 'pages'])->get());
    }


    #[OAT\Post(
        path: '/api/users',
        description: 'Creates a new user record in the system and returns the user resource.',
        summary: 'Create a new user',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        tags: ['Users'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'User created successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation errors (e.g. email already exists)'
            )
        ]
    )]
    public function store(RegisterRequest $request, RegistrationAction $action)
    {
        $user = DB::transaction(callback: fn() => $action->handle($request));
        return UserResource::make($user);
    }


    #[OAT\Get(
        path: '/api/users/{id}',
        description: 'Returns a single user resource by their ID.',
        summary: 'Get user details',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the user to retrieve',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful operation',
                content: new OAT\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OAT\Response(response: 404, description: 'User not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function show(User $user)
    {
        return UserResource::make($user);
    }


    #[OAT\Put(
        path: '/api/users/{id}',
        description: 'Updates the user data. Requires authorization via UserPolicy.',
        summary: 'Update user profile',
        security: [['bearerAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        tags: ['Users'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the user to update',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'User updated successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OAT\Response(response: 403, description: 'Forbidden - You cannot update this user'),
            new OAT\Response(response: 404, description: 'User not found'),
            new OAT\Response(response: 422, description: 'Validation error'),
            new OAT\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function update(RegisterRequest $request, User $user, UpdateUserAction $action)
    {
        $this->authorize('update', $user);

        $user = DB::transaction(callback: fn() => $action->handle($request, $user));
        return UserResource::make($user);
    }


    #[OAT\Delete(
        path: '/api/users/{id}',
        description: 'Permanently removes a user record from the database by ID.',
        summary: 'Delete a user',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OAT\Parameter(
                name: 'id',
                description: 'ID of the user to delete',
                in: 'path',
                required: true,
                schema: new OAT\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'User deleted successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'User deleted')
                    ]
                )
            ),
            new OAT\Response(response: 404, description: 'User not found'),
            new OAT\Response(response: 401, description: 'Unauthenticated'),
            new OAT\Response(response: 403, description: 'Forbidden (Unauthorized)')
        ]
    )]
    public function destroy(User $user)
    {
        $user->delete();

        Log::debug('User removed with id', [$user->id]);

        return response()->json(['message' => 'User deleted']);
    }
}
