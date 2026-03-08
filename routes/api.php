<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Page\DeletePageController;
use App\Http\Controllers\Page\ShowPageController;
use App\Http\Controllers\Page\StorePageController;
use App\Http\Controllers\Page\UpdatePageController;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Setting\SettingController;

Route::prefix('admin')
    ->group(function () {
        Route::resource('posts', PostController::class);

        Route::post('/pages', StorePageController::class);
        Route::get('/pages/{page}', ShowPageController::class);
        Route::put('/pages/{page}', UpdatePageController::class);
        Route::delete('/pages/{page}', DeletePageController::class);

        Route::resource('settings', SettingController::class)
            ->only(['index', 'store', 'update', 'destroy', 'show']);

        Route::post('/comments', [CommentController::class, 'store']);
        Route::get('/comments', [CommentController::class, 'index']);
        Route::get('/comments/{comment}', [CommentController::class, 'show']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
