<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Jobs\NotifyAdminsAboutLoginJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function handle(LoginRequest $request, CreateAccessToken $accessToken): LoginResource
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $accessToken->handle($user);

        Log::debug('Access token create and user found');

        NotifyAdminsAboutLoginJob::dispatch($user);

        return new LoginResource([
            'user' => $user,
            'token' => $token,
        ]);
    }
}
