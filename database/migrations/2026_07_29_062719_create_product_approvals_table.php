<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_approvals', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('reviewer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action', 50);

            $table->string('decision', 30)->nullable();

            $table->string('status_before', 50);

            $table->string('status_after', 50);

            $table->text('remarks')->nullable();

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);

            $table->index(['decision']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_approvals');
    }
};
