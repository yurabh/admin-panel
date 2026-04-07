<?php

namespace App\Actions\Auth;

use App\Models\User;

class CreateAccessToken
{
    public function handle(User $user, ?string $tokenName = null): string
    {
        $tokenName ??= 'Token ' . now()->toIso8601String();

        return $user->createToken($tokenName)->plainTextToken;
    }
}
