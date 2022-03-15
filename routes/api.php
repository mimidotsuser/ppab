<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductFilterController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

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


Route::group(['prefix' => 'v1/auth'], function () {
    Route::post('/login', [LoginController::class, 'authenticate']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store']);
    Route::post('/reset-password/{token}', [ResetPasswordController::class, 'store']);
});

Route::group(['middleware' => ['auth:sanctum'], 'prefix' => 'v1'], function () {

    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::apiResource('customers', CustomerController::class);

    Route::get('product-categories', [ProductCategoryController::class, 'index']);
    Route::apiResource('products', ProductController::class);
    Route::get('warehouses', [WarehouseController::class, 'index']);

    Route::apiResource('product-categories.products', ProductFilterController::class);
});

Route::fallback(function () {
    return response(['error' => 'Resource not found'], 404);
});
