<?php

namespace App\Actions\Auth;

use App\Events\RegistrationEvent;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class RegistrationAction
{
    public function __construct()
    {
    }

    public function handle(RegisterRequest $request): User
    {
        $data = $request->validated();

        $user = User::create($data);

        Log::debug('User created with id', [$user->id]);

        Event::dispatch(new RegistrationEvent($user));

        return $user;
    }
}
