<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('transaction_type', 50);

            $table->string('movement_type', 10);

            $table->integer('quantity');

            $table->integer('stock_before');

            $table->integer('stock_after');

            $table->nullableMorphs('reference');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'product_variant_id',
                'transaction_type'
            ]);

            $table->index([
                'product_variant_id',
                'created_at'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
