<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutAction
{
    public function __construct()
    {
    }

    public function handle(PersonalAccessToken $accessToken): void
    {
        $accessToken->deleteOrFail();

        Log::debug('Access token was deleted');
    }
}
