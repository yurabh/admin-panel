<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->subscribed(User::SUBSCRIPTION_NAME)) {
            return response()->json([
                'error' => 'subscription_required',
                'message' => 'This content is for subscribers only.',
                'action' => 'redirect_to_pricing',
            ], 403);
        }

        return $next($request);
    }
}
