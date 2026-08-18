<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Supplier\SupplierController;
use App\Http\Controllers\Api\V1\Supplier\PublicSupplierController;
use App\Http\Controllers\Api\V1\Admin\SupplierApprovalController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Product\PublicCategoryController;
use App\Http\Controllers\Api\V1\Admin\BrandController;
use App\Http\Controllers\Api\V1\Product\PublicBrandController;
use App\Http\Controllers\Api\V1\Admin\UnitController;
use App\Http\Controllers\Api\V1\Product\PublicUnitController;
use App\Http\Controllers\Api\V1\Product\PublicProductController;
use App\Http\Controllers\Api\V1\Rfq\RfqController;
use App\Http\Controllers\Api\V1\Review\ReviewController;
use App\Http\Controllers\Api\V1\Deal\DealController;
use App\Http\Controllers\Api\V1\Deal\DealAlertController;
use App\Http\Controllers\Api\V1\Newsletter\NewsletterController;
use App\Http\Controllers\Api\V1\Support\ContactController;
use App\Http\Controllers\Api\V1\Order\OrderController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Supplier\SupplierOrderController;
use App\Http\Controllers\Api\V1\Supplier\SupplierDashboardController;
use App\Http\Controllers\Api\V1\Admin\RfqController as AdminRfqController;
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
use App\Http\Controllers\Api\V1\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Api\V1\Admin\ContactMessageController;
use App\Http\Controllers\Api\V1\Admin\DealController as AdminDealController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
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
            'products',
            [LookupController::class, 'products']
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

    Route::get('/products', [PublicProductController::class, 'index']);
    Route::get('/products/{slug}', [PublicProductController::class, 'show']);

    Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

    // No public supplier directory — sellers stay anonymous to buyers
    // so buyers can't identify and route around the platform to order
    // directly from a supplier. A profile is only reachable by its
    // public Seller ID, surfaced from product listings.
    Route::get('/sellers/featured', [PublicSupplierController::class, 'featured']);
    Route::get('/sellers/{sellerId}', [PublicSupplierController::class, 'show']);

    Route::post('/rfqs', [RfqController::class, 'store']);

    Route::get('/deals', [DealController::class, 'index']);
    Route::get('/deals/{deal}', [DealController::class, 'show']);
    Route::post('/deal-alerts/subscribe', [DealAlertController::class, 'store']);

    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);

    Route::post('/contact', [ContactController::class, 'store']);

    Route::prefix('auth')->group(function () {

        // Public
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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

        Route::get(
            '/supplier/me',
            [SupplierController::class, 'me']
        );

        Route::post(
            '/checkout',
            [OrderController::class, 'store']
        );

        Route::get(
            '/orders',
            [OrderController::class, 'index']
        );

        Route::get(
            '/orders/{uuid}',
            [OrderController::class, 'show']
        );

        Route::patch(
            '/orders/{uuid}/cancel',
            [OrderController::class, 'cancel']
        );

        Route::get(
            '/rfqs',
            [RfqController::class, 'index']
        );

        Route::get(
            '/rfqs/{uuid}',
            [RfqController::class, 'show']
        );

        Route::get(
            '/products/{product}/reviews/eligibility',
            [ReviewController::class, 'eligibility']
        );

        Route::post(
            '/products/{product}/reviews',
            [ReviewController::class, 'store']
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
            '/admin/suppliers',
            [SupplierController::class, 'index']
        );

        Route::get(
            '/admin/suppliers/pending',
            [SupplierController::class, 'pending']
        );

        Route::get(
            '/admin/suppliers/approved',
            [SupplierController::class, 'approved']
        );

        Route::get(
            '/admin/suppliers/{supplier}',
            [SupplierController::class, 'show']
        );

        Route::put(
            '/admin/suppliers/{supplier}',
            [SupplierController::class, 'update']
        );

    });

    Route::middleware([
        'auth:sanctum',
        'role:Super Admin|Admin'
    ])->prefix('admin')->group(function () {

        Route::apiResource('categories', CategoryController::class);

        Route::get('/orders', [AdminOrderController::class, 'index']);

        Route::get('/orders/{uuid}', [AdminOrderController::class, 'show']);

        Route::patch(
            '/seller-orders/{uuid}/status',
            [AdminOrderController::class, 'updateSellerOrderStatus']
        );

        Route::patch(
            '/orders/{uuid}/cancel',
            [AdminOrderController::class, 'cancel']
        );

        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        Route::get('/rfqs', [AdminRfqController::class, 'index']);

        Route::get('/rfqs/{uuid}', [AdminRfqController::class, 'show']);

        Route::patch(
            '/rfqs/{uuid}/respond',
            [AdminRfqController::class, 'respond']
        );

        Route::get('/newsletter-subscribers', [AdminNewsletterController::class, 'index']);

        Route::get('/contact-messages', [ContactMessageController::class, 'index']);
        Route::get('/contact-messages/{uuid}', [ContactMessageController::class, 'show']);
        Route::patch('/contact-messages/{uuid}/status', [ContactMessageController::class, 'updateStatus']);

        Route::get('/deals', [AdminDealController::class, 'index']);
        Route::post('/deals', [AdminDealController::class, 'store']);
        Route::get('/deals/alert-subscriptions', [AdminDealController::class, 'alertSubscriptions']);
        Route::get('/deals/{deal}', [AdminDealController::class, 'show']);
        Route::put('/deals/{deal}', [AdminDealController::class, 'update']);
        Route::delete('/deals/{deal}', [AdminDealController::class, 'destroy']);

        Route::get('/customers', [CustomerController::class, 'index']);

    });

    Route::middleware([
        'auth:sanctum',
        'role:Supplier',
    ])->prefix('supplier')->group(function () {

        Route::get('/dashboard', [SupplierDashboardController::class, 'index']);

        Route::get('/orders', [SupplierOrderController::class, 'index']);

        Route::get('/orders/{uuid}', [SupplierOrderController::class, 'show']);

        Route::patch(
            '/orders/{uuid}/status',
            [SupplierOrderController::class, 'updateStatus']
        );

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

        // Create/list/view/edit/delete own products — ownership is
        // enforced in ProductController via ScopesToOwnSupplier.
        Route::apiResource('products', ProductController::class);

        // A supplier may adjust their own product's stock, but not
        // approve, publish, archive, restore or feature it — those
        // are moderation/marketing decisions, kept admin-only below.
        Route::patch(
            'products/{product}/stock',
            [ProductController::class, 'updateStock']
        );

    });

    Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:Super Admin|Admin'])
    ->group(function () {

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

        // Moderator queue and verdicts — a supplier may submit their
        // own product (see the shared group above) but never approve,
        // reject, or see another supplier's queue/stats/history.
        Route::get(
            'products/approval/pending',
            [ProductApprovalController::class, 'pending']
        );

        Route::post(
            'products/{product}/approval/approve',
            [ProductApprovalController::class, 'approve']
        );

        Route::post(
            'products/{product}/approval/reject',
            [ProductApprovalController::class, 'reject']
        );

        Route::get(
            'products/approval/statistics',
            [ProductApprovalController::class, 'statistics']
        );

        Route::get(
            'products/suppliers/{user}/approval/history',
            [ProductApprovalController::class, 'supplierHistory']
        );

        // Duplicate status-mutation surface (ProductStatusController)
        // — same admin-only rule as the ProductController verbs above.
        Route::post(
            'products/{product}/status/publish',
            [ProductStatusController::class, 'publish']
        );

        Route::post(
            'products/{product}/status/unpublish',
            [ProductStatusController::class, 'unpublish']
        );

        Route::post(
            'products/{product}/status/archive',
            [ProductStatusController::class, 'archive']
        );

        Route::post(
            'products/{product}/status/restore',
            [ProductStatusController::class, 'restore']
        );

        // Unscoped cross-supplier catalog query/statistics — not
        // needed by any supplier UI, and would otherwise leak every
        // supplier's cost prices and stock to any other supplier.
        Route::get(
            'products/query',
            [ProductQueryController::class, 'index']
        );

        Route::get(
            'products/query/statistics',
            [ProductQueryController::class, 'statistics']
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
            'variants/{variant_id}',
            [ProductVariantController::class, 'show']
        );

        Route::put(
            'variants/{variant_id}',
            [ProductVariantController::class, 'update']
        );

        Route::patch(
            'variants/{variant_id}',
            [ProductVariantController::class, 'update']
        );

        Route::delete(
            'variants/{variant_id}',
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

        Route::prefix(
    'products/{product}/variants/{variant_id}'
)
    ->group(function () {

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

        Route::prefix('variants/{variant_id}/inventory')->group(function () {

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

        // A supplier may submit their own product for review and see
        // its own approval history/timeline — not another supplier's,
        // and never the moderator queue or the approve/reject verbs
        // themselves (moved to the admin-only group below).
        Route::prefix('products')->group(function () {

            Route::post(
                '{product}/approval/submit',
                [ProductApprovalController::class, 'submit']
            );

            Route::get(
                '{product}/approval/history',
                [ProductApprovalController::class, 'history']
            );

            Route::get(
                '{product}/approval/timeline',
                [ProductApprovalController::class, 'timeline']
            );

        });

        Route::prefix('products/{product}/status')->group(function () {

            Route::get(
                '/history',
                [ProductStatusController::class, 'history']
            );

        });
    });


});
