<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_status_histories', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action',50);

            $table->string('status_before',50);

            $table->string('status_after',50);

            $table->text('remarks')->nullable();

            $table->timestamp('performed_at');

            $table->timestamps();

            $table->index([
                'product_id',
                'performed_at'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_status_histories');
    }
};
