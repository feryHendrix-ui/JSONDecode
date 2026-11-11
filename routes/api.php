<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\OpenLibraryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show'])->whereNumber('book');
    Route::get('/stats', [BookController::class, 'stats']);
    Route::get('/open-library', OpenLibraryController::class)->middleware('throttle:20,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::middleware(['abilities:books:write', 'can:manage-books'])->group(function () {
            Route::post('/books', [BookController::class, 'store']);
            Route::patch('/books/{book}', [BookController::class, 'update'])->whereNumber('book');
            Route::delete('/books/{book}', [BookController::class, 'destroy'])->whereNumber('book');
        });
    });
});
