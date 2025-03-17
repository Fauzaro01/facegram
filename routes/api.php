<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function() {

    // AUTH routes
    Route::prefix('/auth')->controller(AuthController::class)->group(function() {
        Route::get('/', 'index')->name('auth.index');
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
    });

    Route::prefix('posts')->controller(PostController::class)->middleware('auth:sanctum')->group(function() {
        Route::get('/', 'index')->name('post.index');
        Route::post('/', 'store')->name('post.store');
        Route::delete('/{id}', 'delete')->name('post.delete');
    });

});