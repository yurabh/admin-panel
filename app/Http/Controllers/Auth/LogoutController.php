<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, LogoutAction $action)
    {
        $token = $request->user()->currentAccessToken();

        DB::transaction(callback: fn() => $action->handle($token));

        return response()->json(status: 204);
    }
}
