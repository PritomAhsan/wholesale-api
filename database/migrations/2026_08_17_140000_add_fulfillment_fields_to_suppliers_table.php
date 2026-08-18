<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('fulfillment_region')->nullable()->after('description');
            $table->string('typical_lead_time')->nullable()->after('fulfillment_region');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_region', 'typical_lead_time']);
        });
    }
};
