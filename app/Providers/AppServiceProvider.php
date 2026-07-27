<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('product', function ($value) {
            return Product::where('uuid', $value)->firstOrFail();
        });

        Route::bind('variant', function ($value) {
            return ProductVariant::where('uuid', $value)->firstOrFail();
        });
    }
}
