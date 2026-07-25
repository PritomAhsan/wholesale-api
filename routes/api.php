<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Admin\SupplierApprovalController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Product\PublicCategoryController;


Route::prefix('v1')->group(function () {

    Route::get('/categories', [PublicCategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [PublicCategoryController::class, 'show']);

    Route::prefix('auth')->group(function () {

        // Public
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', []);
        Route::post('/reset-password', []);

        // Protected
        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

        });

    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::post(
            '/supplier/register',
            [SupplierController::class,'register']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])->group(function () {

        Route::post(
            '/admin/suppliers/{supplier}/approve',
            [SupplierApprovalController::class, 'approve']
        );

        Route::post(
            '/admin/suppliers/{supplier}/reject',
            [SupplierApprovalController::class, 'reject']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])->group(function () {

        Route::get(
            '/admin/suppliers/pending',
            [SupplierController::class, 'pending']
        );

        Route::get(
            '/admin/suppliers/approved',
            [SupplierController::class, 'approved']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])->prefix('admin')->group(function () {

        Route::apiResource('categories', CategoryController::class);

    });

});
