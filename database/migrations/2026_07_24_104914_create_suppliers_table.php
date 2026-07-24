<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('company_name');

            $table->string('company_slug')->unique();

            $table->enum('business_type', [
                'manufacturer',
                'wholesaler',
                'distributor',
                'exporter',
                'retailer'
            ]);

            $table->string('contact_person');

            $table->string('email');

            $table->string('phone',30);

            $table->string('website')->nullable();

            $table->string('registration_number')->nullable();

            $table->string('tax_number')->nullable();

            $table->text('description')->nullable();

            $table->string('logo')->nullable();

            $table->string('banner')->nullable();

            $table->enum('status',[
                'pending',
                'approved',
                'rejected',
                'suspended'
            ])->default('pending');

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('company_name');
            $table->index('business_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
