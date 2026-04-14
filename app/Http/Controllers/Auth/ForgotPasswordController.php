<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Password;
use OpenApi\Attributes as OAT;

class ForgotPasswordController extends Controller
{
    #[OAT\Post(
        path: '/api/forgot/password',
        description: 'Sends a password reset email to the user with a token.',
        summary: 'Send password reset link',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/ForgotPasswordRequest')
        ),
        tags: ['Authentication'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Reset link sent successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'We have emailed your password reset link.'),
                    ]
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Unable to send reset link',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'error', type: 'string', example: 'We can\'t find a user with that email address.'),
                    ]
                )
            ),
            new OAT\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(ForgotPasswordRequest $request)
    {
        $status = Password::broker()->sendResetLink(
            $request->validated()
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['error' => __($status)], 400);
    }
}
