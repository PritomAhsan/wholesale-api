<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('product_name');

            $table->string('preferred_supplier_name')->nullable();

            $table->decimal('quantity', 15, 2);

            $table->string('unit')->default('Pieces');

            $table->decimal('budget', 15, 2)->nullable();

            $table->string('destination_country');

            $table->date('required_delivery_date')->nullable();

            $table->text('message');

            $table->string('attachment_path')->nullable();

            $table->string('contact_name');

            $table->string('contact_email');

            $table->string('contact_phone')->nullable();

            $table->enum('status', [
                'pending',
                'quoted',
                'accepted',
                'rejected',
                'closed',
            ])->default('pending');

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};
