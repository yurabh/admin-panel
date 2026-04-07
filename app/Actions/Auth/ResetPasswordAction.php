<?php

namespace App\Actions\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordAction
{
    public function handle(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->tryResetPassword($request);

        if ($status === Password::PASSWORD_RESET) {
            Log::debug("Password reset successfully");
            return response()->json(['Password reset successfully']);
        }

        Log::debug('Token reset failed');

        return response()->json(['Token reset failed'], 422);
    }

    private function tryResetPassword(ResetPasswordRequest $request): string
    {
        return Password::broker()->reset(
            $request->validated(),
            function ($user, $password) {
                $user->password = $password;
                $user->setRememberToken(Str::random(60));
                $user->save();
                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );
    }
}
