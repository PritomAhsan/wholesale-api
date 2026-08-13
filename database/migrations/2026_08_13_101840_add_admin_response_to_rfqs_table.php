<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {

            $table->text('admin_response')->nullable()->after('message');

            $table->timestamp('responded_at')->nullable()->after('status');

            $table->foreignId('responded_by')
                ->nullable()
                ->after('responded_at')
                ->constrained('users')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {

            $table->dropConstrainedForeignId('responded_by');

            $table->dropColumn(['admin_response', 'responded_at']);

        });
    }
};
