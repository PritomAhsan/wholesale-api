<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('reason', [
                'not_received',
                'damaged',
                'wrong_item',
                'quantity_mismatch',
                'counterfeit',
                'late_shipment',
                'seller_not_responding',
                'refund_not_received',
                'other',
            ]);

            $table->text('description');

            $table->enum('status', ['open', 'resolved', 'rejected'])->default('open');

            // Set once an admin resolves it.
            $table->enum('resolution', [
                'refund_full',
                'refund_partial',
                'replacement',
                'no_action',
            ])->nullable();

            $table->decimal('resolution_amount', 15, 2)->nullable();

            $table->text('resolution_note')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // One active dispute per seller order at a time — a buyer
            // can't open a second one while the first is still open.
            $table->index(['seller_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
