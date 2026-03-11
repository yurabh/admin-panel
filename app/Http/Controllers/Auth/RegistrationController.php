<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAccessToken;
use App\Actions\Auth\RegistrationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\LoginResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest    $request,
                             CreateAccessToken  $accessToken,
                             RegistrationAction $action)
    {
        $user = DB::transaction(callback: fn() => $action->handle($request));

        $token = $accessToken->handle($user);

        Log::debug('Token was created');

        return new LoginResource([
            'user' => $user,
            'token' => $token
        ]);
    }
}
