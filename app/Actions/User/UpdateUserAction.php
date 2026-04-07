<?php

namespace App\Actions\User;

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UpdateUserAction
{
    public function handle(RegisterRequest $request, User $user): User
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        Log::debug('User updated with id', [$user->id]);

        return $user;
    }
}
