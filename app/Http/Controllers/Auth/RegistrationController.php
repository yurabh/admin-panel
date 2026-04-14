<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAccessToken;
use App\Actions\Auth\RegistrationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\LoginResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OAT;

class RegistrationController extends Controller
{
    #[OAT\Post(
        path: '/api/register',
        description: 'Creates a new user account, generates an access token, and returns user data with the token.',
        summary: 'Register a new user',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        tags: ['Authentication'],
        responses: [
            new OAT\Response(
                response: 201,
                description: 'User registered and logged in successfully',
                content: new OAT\JsonContent(ref: '#/components/schemas/LoginResource')
            ),
            new OAT\Response(
                response: 422,
                description: 'Validation error (e.g., email already taken)'
            ),
        ]
    )]
    public function __invoke(RegisterRequest $request,
        CreateAccessToken $accessToken,
        RegistrationAction $action)
    {
        $user = DB::transaction(callback: fn () => $action->handle($request));

        $token = $accessToken->handle($user);

        Log::debug('Token was created');

        return new LoginResource([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
