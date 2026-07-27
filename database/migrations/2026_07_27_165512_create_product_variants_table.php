<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('sku')->unique();

            $table->string('barcode')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('cost_price',12,2)->default(0);

            $table->decimal('selling_price',12,2);

            $table->decimal('compare_at_price',12,2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            $table->integer('stock_quantity')->default(0);

            $table->integer('low_stock_quantity')->default(5);

            /*
            |--------------------------------------------------------------------------
            | Physical
            |--------------------------------------------------------------------------
            */

            $table->decimal('weight',12,2)->nullable();

            $table->decimal('length',12,2)->nullable();

            $table->decimal('width',12,2)->nullable();

            $table->decimal('height',12,2)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'product_variants'
        );
    }
};
