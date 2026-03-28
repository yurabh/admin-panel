<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Page\DeletePageController;
use App\Http\Controllers\Page\ShowPageController;
use App\Http\Controllers\Page\StorePageController;
use App\Http\Controllers\Page\UpdatePageController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Tag\TagController;
use App\Http\Controllers\User\UserController;

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        Route::prefix('posts')->group(function () {
            Route::get('search', [PostController::class, 'search']);
            Route::get('filter', [PostController::class, 'filter']);
            Route::get('sorted-by-date', [PostController::class, 'getSortedByDatePublishedAt']);
            Route::get('category/{categoryId}', [PostController::class, 'getByCategoryId']);
        });
        Route::resource('posts', PostController::class);

        Route::post('/pages', StorePageController::class);
        Route::get('/pages/{page}', ShowPageController::class);
        Route::put('/pages/{page}', UpdatePageController::class);
        Route::delete('/pages/{page}', DeletePageController::class);

        Route::resource('settings', SettingController::class)
            ->only(['index', 'store', 'update', 'destroy', 'show']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::get('/tags/{tag}', [TagController::class, 'show']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
    });


Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/logout', LogoutController::class);

    Route::apiResource('users', UserController::class);

    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/comments', [CommentController::class, 'index']);
    Route::get('/comments/{comment}', [CommentController::class, 'show']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});


Route::post('/login', AuthController::class);
Route::post('/register', RegistrationController::class);
Route::post('/forgot/password', ForgotPasswordController::class);
Route::post('/password/reset', ResetPasswordController::class)
    ->name('password.reset');
