<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;

/*
|--------------------------------------------------------------------------
| API Routes - Dream Bending
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── Auth (Public) ───────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('register',         [AuthController::class, 'register']);
        Route::post('login',            [AuthController::class, 'login']);
        Route::post('forgot-password',  [PasswordController::class, 'forgotPassword']);
        Route::post('reset-password',   [PasswordController::class, 'resetPassword']);
    });

    // ─── Products (Public) ───────────────────────────────────────────
    Route::get('products',              [ProductController::class, 'index']);
    Route::get('products/{slug}',       [ProductController::class, 'show']);
    Route::get('categories',            [ProductController::class, 'categories']);

    // ─── Authenticated Routes ────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',       [AuthController::class, 'logout']);
            Route::post('logout-all',   [AuthController::class, 'logoutAll']);
            Route::get('me',            [AuthController::class, 'me']);
            Route::put('profile',       [AuthController::class, 'updateProfile']);
            Route::put('password',      [PasswordController::class, 'changePassword']);
        });

        // Cart
        Route::get('cart',              [CartController::class, 'index']);
        Route::post('cart',             [CartController::class, 'store']);
        Route::put('cart/{cart}',       [CartController::class, 'update']);
        Route::delete('cart/{cart}',    [CartController::class, 'destroy']);
        Route::delete('cart',           [CartController::class, 'clear']);

        // Wishlist
        Route::get('wishlist',          [WishlistController::class, 'index']);
        Route::post('wishlist/toggle',  [WishlistController::class, 'toggle']);

        // Orders
        Route::get('orders',            [OrderController::class, 'index']);
        Route::post('orders',           [OrderController::class, 'store']);
        Route::get('orders/{order}',    [OrderController::class, 'show']);

        // ─── Admin Routes ─────────────────────────────────────────────
        Route::middleware('admin')->prefix('admin')->group(function () {

            // Dashboard
            Route::get('dashboard',     [AdminOrderController::class, 'dashboard']);

            // Products CRUD
            Route::get('products',      [AdminProductController::class, 'index']);
            Route::post('products',     [AdminProductController::class, 'store']);
            Route::get('products/{product}', [AdminProductController::class, 'show']);
            Route::put('products/{product}', [AdminProductController::class, 'update']);
            Route::delete('products/{product}', [AdminProductController::class, 'destroy']);
            Route::post('products/{id}/restore', [AdminProductController::class, 'restore']);

            // Categories
            Route::post('categories',   [AdminProductController::class, 'storeCategory']);
            Route::put('categories/{category}', [AdminProductController::class, 'updateCategory']);

            // Orders
            Route::get('orders',        [AdminOrderController::class, 'index']);
            Route::get('orders/{order}', [AdminOrderController::class, 'show']);
            Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        });
    });
});
