<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Admin\SupplierApprovalController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Product\PublicCategoryController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Product\PublicBrandController;
use App\Http\Controllers\Api\V1\Admin\UnitController;
use App\Http\Controllers\Api\V1\Product\PublicUnitController;
use App\Http\Controllers\Api\V1\Admin\ProductAttributeController;
use App\Http\Controllers\Api\V1\Product\PublicProductAttributeController;
use App\Http\Controllers\Api\V1\Admin\ProductAttributeValueController;
use App\Http\Controllers\Api\V1\Product\PublicProductAttributeValueController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ProductVariantController;
use App\Http\Controllers\Api\V1\Admin\ProductImageController;
use App\Http\Controllers\Api\V1\Admin\ProductVariantImageController;
use App\Http\Controllers\Api\V1\Admin\ProductVariantInventoryController;
use App\Http\Controllers\Api\V1\Admin\InventoryReportController;
use App\Http\Controllers\Api\V1\Admin\ProductApprovalController;
use App\Http\Controllers\Api\V1\Admin\ProductStatusController;
use App\Http\Controllers\Api\V1\Admin\ProductQueryController;
use App\Http\Controllers\Api\Admin\Lookup\LookupController;

Route::prefix('lookups')
    ->group(function () {

        Route::get(
            'categories',
            [LookupController::class, 'categories']
        );

        Route::get(
            'brands',
            [LookupController::class, 'brands']
        );

        Route::get(
            'units',
            [LookupController::class, 'units']
        );

        Route::get(
            'suppliers',
            [LookupController::class, 'suppliers']
        );

        Route::get(
            'attributes',
            [LookupController::class, 'attributes']
        );

        Route::get(
            'attribute-values',
            [LookupController::class, 'attributeValues']
        );

    });

Route::prefix('v1')->group(function () {

    Route::get('/categories', [PublicCategoryController::class, 'index']);
    Route::get('/categories/{category:slug}', [PublicCategoryController::class, 'show']);

    Route::get('/brands', [PublicBrandController::class, 'index']);
    Route::get('/brands/featured', [PublicBrandController::class, 'featured']);
    Route::get('/brands/{brand:slug}', [PublicBrandController::class, 'show']);

    Route::get(
        '/units',
        [PublicUnitController::class,'index']
    );

    Route::get(
        '/units/{unit}',
        [PublicUnitController::class,'show']
    );

    Route::get(
        '/product-attributes',
        [PublicProductAttributeController::class, 'index']
    );

    Route::get(
        '/product-attributes/{productAttribute}/values',
        [PublicProductAttributeValueController::class, 'index']
    );

    Route::prefix('auth')->group(function () {

        // Public
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        // TODO: not implemented yet — needs AuthService::forgotPassword/resetPassword,
        // mail config, and reset-token handling. Left out so a client hitting these
        // gets a clean 404 instead of a 500 from an empty route action.
        // Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        // Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])
    ->group(function () {

        Route::prefix('admin')->group(function () {

            Route::apiResource('brands', BrandController::class);

            Route::post(
                'brands/{brand}',
                [BrandController::class, 'update']
            );

            Route::patch(
                'brands/{brand}/toggle-status',
                [BrandController::class, 'toggleStatus']
            );

            Route::patch(
                'brands/{brand}/toggle-featured',
                [BrandController::class, 'toggleFeatured']
            );

            Route::patch(
                'brands/{uuid}/restore',
                [BrandController::class, 'restore']
            );

            Route::delete(
                'brands/{uuid}/force-delete',
                [BrandController::class, 'forceDelete']
            );

        });

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])
    ->prefix('admin')
    ->group(function(){

        Route::apiResource(
            'units',
            UnitController::class
        );

        Route::patch(
            'units/{unit}/toggle-status',
            [UnitController::class,'toggleStatus']
        );

        Route::patch(
            'units/{uuid}/restore',
            [UnitController::class,'restore']
        );

        Route::delete(
            'units/{uuid}/force-delete',
            [UnitController::class,'forceDelete']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])
    ->prefix('admin')
    ->group(function () {

        Route::apiResource(
            'product-attributes',
            ProductAttributeController::class
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])
    ->prefix('admin')
    ->group(function () {

        Route::get(
            'product-attributes/{productAttribute}/values',
            [ProductAttributeValueController::class, 'index']
        );

        Route::post(
            'product-attributes/{productAttribute}/values',
            [ProductAttributeValueController::class, 'store']
        );

        Route::get(
            'product-attribute-values/{productAttributeValue}',
            [ProductAttributeValueController::class, 'show']
        );

        Route::put(
            'product-attribute-values/{productAttributeValue}',
            [ProductAttributeValueController::class, 'update']
        );

        Route::delete(
            'product-attribute-values/{productAttributeValue}',
            [ProductAttributeValueController::class, 'destroy']
        );

        Route::patch(
            'product-attribute-values/{productAttributeValue}/toggle-status',
            [ProductAttributeValueController::class, 'toggleStatus']
        );

        Route::patch(
            'product-attribute-values/{uuid}/restore',
            [ProductAttributeValueController::class, 'restore']
        );

        Route::delete(
            'product-attribute-values/{uuid}/force-delete',
            [ProductAttributeValueController::class, 'forceDelete']
        );

    });

    Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:Super Admin|Admin|Supplier'])
    ->group(function () {

        Route::apiResource('products', ProductController::class);

        Route::patch(
            'products/{product}/restore',
            [ProductController::class, 'restore']
        );

        Route::delete(
            'products/{product}/force-delete',
            [ProductController::class, 'forceDelete']
        );

        Route::patch(
            'products/{product}/approve',
            [ProductController::class, 'approve']
        );

        Route::patch(
            'products/{product}/reject',
            [ProductController::class, 'reject']
        );

        Route::patch(
            'products/{product}/publish',
            [ProductController::class, 'publish']
        );

        Route::patch(
            'products/{product}/archive',
            [ProductController::class, 'archive']
        );

        Route::patch(
            'products/{product}/featured',
            [ProductController::class, 'toggleFeatured']
        );

        Route::patch(
            'products/{product}/stock',
            [ProductController::class, 'updateStock']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin|Supplier',
    ])
    ->prefix('admin')
    ->group(function () {

        Route::prefix('products/{product}')
            ->group(function () {

            Route::get(
                'variants',
                [ProductVariantController::class, 'index']
            );

            Route::post(
                'variants',
                [ProductVariantController::class, 'store']
            );

            Route::get(
                'variants/{variant}',
                [ProductVariantController::class, 'show']
            );

            Route::put(
                'variants/{variant}',
                [ProductVariantController::class, 'update']
            );

            Route::patch(
                'variants/{variant}',
                [ProductVariantController::class, 'update']
            );

            Route::delete(
                'variants/{variant}',
                [ProductVariantController::class, 'destroy']
            );

            Route::patch(
                'variants/{uuid}/restore',
                [ProductVariantController::class, 'restore']
            );

            Route::delete(
                'variants/{uuid}/force-delete',
                [ProductVariantController::class, 'forceDelete']
            );

        });

        Route::prefix('products/{product}')->group(function () {

            Route::post(
                'images',
                [ProductImageController::class, 'upload']
            );

            Route::put(
                'images/{image}',
                [ProductImageController::class, 'update']
            );

            Route::patch(
                'images/{image}',
                [ProductImageController::class, 'update']
            );

            Route::delete(
                'images/{image}',
                [ProductImageController::class, 'destroy']
            );

            Route::patch(
                'images/{image}/primary',
                [ProductImageController::class, 'setPrimary']
            );

            Route::patch(
                'images/reorder',
                [ProductImageController::class, 'reorder']
            );

        });

        Route::prefix('products/{product}/variants/{variant}')->group(function () {

            Route::post(
                'images',
                [ProductVariantImageController::class, 'upload']
            );

            Route::delete(
                'images/{image}',
                [ProductVariantImageController::class, 'destroy']
            );

            Route::patch(
                'images/{image}/primary',
                [ProductVariantImageController::class, 'setPrimary']
            );

        });

        Route::prefix('variants/{variant}/inventory')->group(function () {

            Route::get(
                '/',
                [ProductVariantInventoryController::class, 'show']
            );

            Route::post(
                '/increase',
                [ProductVariantInventoryController::class, 'increase']
            );

            Route::post(
                '/decrease',
                [ProductVariantInventoryController::class, 'decrease']
            );

            Route::post(
                '/adjust',
                [ProductVariantInventoryController::class, 'adjust']
            );

            Route::get(
                '/history',
                [ProductVariantInventoryController::class, 'history']
            );

        });

        Route::prefix('inventory')->group(function () {

            Route::get(
                'dashboard',
                [InventoryReportController::class, 'dashboard']
            );

            Route::get(
                'reports/low-stock',
                [InventoryReportController::class, 'lowStock']
            );

            Route::get(
                'reports/out-of-stock',
                [InventoryReportController::class, 'outOfStock']
            );

            Route::get(
                'reports/value',
                [InventoryReportController::class, 'inventoryValue']
            );

            Route::get(
                'reports/recent-transactions',
                [InventoryReportController::class, 'recentTransactions']
            );

        });

        Route::prefix('products')->group(function () {

            Route::get(
                'approval/pending',
                [ProductApprovalController::class, 'pending']
            );

            Route::post(
                '{product}/approval/submit',
                [ProductApprovalController::class, 'submit']
            );

            Route::post(
                '{product}/approval/approve',
                [ProductApprovalController::class, 'approve']
            );

            Route::post(
                '{product}/approval/reject',
                [ProductApprovalController::class, 'reject']
            );

            Route::get(
                '{product}/approval/history',
                [ProductApprovalController::class, 'history']
            );

            Route::get(
                'products/{product}/approval/timeline',
                [ProductApprovalController::class, 'timeline']
            );

            Route::get(
                'products/approval/statistics',
                [ProductApprovalController::class, 'statistics']
            );

            Route::get(
                'suppliers/{user}/approval/history',
                [ProductApprovalController::class, 'supplierHistory']
            );

        });

        Route::prefix('products/{product}/status')->group(function () {

            Route::post(
                '/publish',
                [ProductStatusController::class, 'publish']
            );

            Route::post(
                '/unpublish',
                [ProductStatusController::class, 'unpublish']
            );

            Route::post(
                '/archive',
                [ProductStatusController::class, 'archive']
            );

            Route::post(
                '/restore',
                [ProductStatusController::class, 'restore']
            );

            Route::get(
                '/history',
                [ProductStatusController::class, 'history']
            );

            Route::get(
                'products',
                [ProductQueryController::class, 'index']
            );

            Route::get(
                'products/statistics',
                [ProductQueryController::class, 'statistics']
            );

        });
    });


});
