<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {

            $table->decimal('commission_amount', 15, 2)->nullable()->after('subtotal');

            $table->decimal('payable_amount', 15, 2)->nullable()->after('commission_amount');

            // Set once this seller order's payable amount has been
            // included in a payout request — prevents the same
            // delivered order from being paid out twice.
            $table->foreignId('payout_id')->nullable()->after('payable_amount')
                ->constrained()->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payout_id');
            $table->dropColumn(['commission_amount', 'payable_amount']);
        });
    }
};
