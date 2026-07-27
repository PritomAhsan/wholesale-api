<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            $table->boolean('is_default')
                ->default(false)
                ->after('is_active');

            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('is_default');

        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            $table->dropColumn([
                'is_default',
                'sort_order',
            ]);

        });
    }
};
