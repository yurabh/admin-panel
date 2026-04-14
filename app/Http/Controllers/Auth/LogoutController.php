<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OAT;

class LogoutController extends Controller
{
    #[OAT\Post(
        path: '/api/logout',
        description: 'Revokes the current access token and logs out the user.',
        summary: 'User logout',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Logged out successfully (No Content)'
            ),
            new OAT\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function __invoke(Request $request, LogoutAction $action)
    {
        $token = $request->user()->currentAccessToken();

        DB::transaction(callback: fn () => $action->handle($token));

        return response()->json(status: 204);
    }
}
