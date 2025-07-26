<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\Web\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Admin routes
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware('auth:admin-api')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);

    // Protected Admin Routes
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::get('/admin/categories', [CategoryController::class, 'index']);
    Route::get('/admin/categories/{id}', [CategoryController::class, 'show']);
    Route::put('/admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);

    Route::apiResource('admin/products', \App\Http\Controllers\Api\ProductController::class);
    // routes/api.php
    Route::apiResource('admin/currencies', \App\Http\Controllers\Api\CurrencyController::class);

    Route::apiResource('admin/orders', \App\Http\Controllers\Api\OrderController::class);
    Route::patch('admin/orders/{id}/status', [\App\Http\Controllers\Api\OrderController::class, 'updateStatus']);


});

// Admin Routes


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/checkout', [CheckoutController::class, 'store']);

// Currency Routes

