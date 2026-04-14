<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OAT;

class ResetPasswordController extends Controller
{
    #[OAT\Post(
        path: '/api/password/reset',
        description: 'Updates the user password using a valid reset token.',
        summary: 'Reset user password',
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(ref: '#/components/schemas/ResetPasswordRequest')
        ),
        tags: ['Authentication'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Your password has been reset.'),
                    ]
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Invalid token or email',
                content: new OAT\JsonContent(
                    properties: [
                        new OAT\Property(property: 'email', type: 'string', example: 'This password reset token is invalid.'),
                    ]
                )
            ),
            new OAT\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(ResetPasswordRequest $request, ResetPasswordAction $action)
    {
        return DB::transaction(fn () => $action->handle($request));
    }
}
