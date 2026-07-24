<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            $table->string('first_name', 100);

            $table->string('last_name', 100)->nullable();

            $table->string('email')->unique();

            $table->string('phone', 30)->nullable()->unique();

            $table->string('password');

            $table->string('avatar')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'inactive',
                'suspended',
                'banned',
            ])->default('pending');

            $table->timestamp('email_verified_at')->nullable();

            $table->timestamp('last_login_at')->nullable();

            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();

            $table->timestamps();

            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('created_at');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
