<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            $table->decimal('wholesale_price', 12, 2)
                ->nullable()
                ->after('selling_price');

            $table->decimal('minimum_order_quantity', 12, 2)
                ->default(1)
                ->after('wholesale_price');

            $table->decimal('maximum_order_quantity', 12, 2)
                ->nullable()
                ->after('minimum_order_quantity');

        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            $table->dropColumn([
                'wholesale_price',
                'minimum_order_quantity',
                'maximum_order_quantity',
            ]);

        });
    }
};
