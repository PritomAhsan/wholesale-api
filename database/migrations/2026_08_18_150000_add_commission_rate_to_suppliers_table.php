<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {

            // Percentage taken by the platform on each seller order,
            // e.g. 10.00 = 10%. Null falls back to the platform default
            // (see Supplier::DEFAULT_COMMISSION_RATE) rather than 0 —
            // an unset rate must never mean "free," only "not yet set."
            $table->decimal('commission_rate', 5, 2)->nullable()->after('typical_lead_time');

        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('commission_rate');
        });
    }
};
