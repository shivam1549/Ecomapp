<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminAuthController;
use App\Http\Controllers\API\CategoryController;

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
});

// Admin Routes


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

