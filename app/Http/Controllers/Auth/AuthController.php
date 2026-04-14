<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAccessToken;
use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use OpenApi\Attributes as OAT;

class AuthController extends Controller
{
    #[OAT\Post(
        path: '/api/login',
        description: 'Authenticates a user and returns an access token with user details.',
        summary: 'User login',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        tags: ['Authentication'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Successful login',
                content: new OAT\JsonContent(ref: '#/components/schemas/LoginResource')
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation error (invalid credentials)'
            ),
        ]
    )]
    public function __invoke(LoginRequest $request,
        CreateAccessToken $accessToken,
        LoginAction $action)
    {
        return $action->handle($request, $accessToken);
    }
}
