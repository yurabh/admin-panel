<?php

namespace App\Actions\User;

use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UpdateUserAction
{
    public function handle(UpdateUserRequest $request, User $user): User
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);

        } else {
            unset($data['password']);
        }

        $user->update($data);

        Log::debug('User updated with id', [$user->id]);

        return $user;
    }
}
