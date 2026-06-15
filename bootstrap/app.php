<?php

use App\Exceptions\BillingException;
use App\Exceptions\PageException;
use App\Exceptions\PostException;
use App\Http\Middleware\CheckSubscriptionMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => IsAdminMiddleware::class,
            'subscribed' => CheckSubscriptionMiddleware::class,
        ]);
        $middleware->api([
        ]);
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            'api/*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PageException $e) {
            $status = $e->getCode() ?: 400;

            return response()->json([
                'message' => 'Page exception was occurred: '.$e->getMessage(),
                'errors_code' => $status,
            ], $status);
        });

        $exceptions->render(function (PostException $e) {
            $status = $e->getCode() ?: 400;

            return response()->json([
                'message' => 'Post exception was occurred: '.$e->getMessage(),
                'errors_code' => $status,
            ], $status);
        });

        $exceptions->render(function (BillingException $e) {
            $status = $e->getCode() ?: 400;

            return response()->json([
                'message' => $e->getMessage(),
                'errors_code' => $status,
            ], $status);
        });
    })->create();
