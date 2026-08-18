<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('communication_rating');
            $table->unsignedTinyInteger('shipping_rating');
            $table->unsignedTinyInteger('packaging_rating');
            $table->text('comment');
            $table->string('status')->default('approved');
            $table->timestamps();

            // One review per buyer per store — a delivered seller order
            // is required to write one, same discipline as product reviews.
            $table->unique(['supplier_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_reviews');
    }
};
