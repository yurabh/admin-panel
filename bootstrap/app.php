<?php

use App\Exceptions\BillingException;
use App\Exceptions\PageException;
use App\Exceptions\PostException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdminMiddleware::class,
            'subscribed' => \App\Http\Middleware\CheckSubscriptionMiddleware::class,
        ]);
        $middleware->api([
        ]);
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PageException $e) {
            return response()->json([
                'message' => 'Page exception was occurred: ' . $e->getMessage()
            ]);
        });

        $exceptions->render(function (PostException $e) {
            return response()->json([
                'message' => 'Post exception was occurred: ' . $e->getMessage()
            ]);
        });

        $exceptions->render(function (BillingException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ]);
        });
    })->create();
