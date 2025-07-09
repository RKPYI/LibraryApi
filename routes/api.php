<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // Book Routes
    Route::prefix('books')->group(function () {
        Route::get('/', [BookController::class, 'index']);
        Route::get('search', [BookController::class, 'search']);
        Route::get('{book}', [BookController::class, 'show']);

        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::post('/', [BookController::class, 'store']);
            Route::put('{book}', [BookController::class, 'update']);
            Route::delete('{book}', [BookController::class, 'destroy']);
        });
    });

    // User Routes
    Route::prefix('users')->middleware('auth:sanctum')->group(function () {
        route::get('/', [UserController::class, 'index'])->middleware('admin');
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
    });

    // Borrow Routes
    Route::prefix('borrows')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [BorrowController::class, 'index']);
        Route::get('{borrow}', [BorrowController::class, 'show']);
        Route::post('/', [BorrowController::class, 'store']);
        Route::get('overdue', [BorrowController::class, 'overdue']);
        Route::put('{borrow}/reject', [BorrowController::class, 'rejectBorrow']);

        Route::middleware('admin')->group(function () {
            Route::put('{borrow}', [BorrowController::class, 'approveBorrow']);
            Route::delete('{borrow}', [BorrowController::class, 'destroy']);
        });
    });

    // Return Routes
    Route::prefix('returns')->middleware('auth:sanctum')->group(function () {
        Route::post('{borrow}/request', [BorrowController::class, 'requestReturn']);

        Route::middleware('admin')->group(function () {
            Route::put('{borrow}/approve', [BorrowController::class, 'approveReturn']);
            Route::put('{borrow}/reject', [BorrowController::class, 'rejectReturn']);
        });
    });

    // Category Routes
    Route::get('categories', [CategoryController::class, 'index']);
    Route::apiResource('categories', CategoryController::class)
        ->except(['index'])
        ->middleware(['auth:sanctum', 'admin']);
});
