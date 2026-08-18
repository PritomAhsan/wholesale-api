<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // bulk = quantity-break pricing, shown as tiers on the product
            // page. flash = time-limited discount. clearance = discount
            // with no end date, cleared once stock runs out.
            $table->string('type');

            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->decimal('discount_price', 12, 2)->nullable();

            // Bulk-tier threshold — null for flash/clearance deals.
            $table->unsignedInteger('min_quantity')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['product_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
