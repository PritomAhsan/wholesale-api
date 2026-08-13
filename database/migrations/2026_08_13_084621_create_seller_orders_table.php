<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_orders', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('seller_order_number')->unique();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('subtotal', 15, 2);

            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
            ])->default('pending');

            $table->string('tracking_number')->nullable();

            $table->string('shipping_carrier')->nullable();

            $table->timestamp('shipped_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_orders');
    }
};
