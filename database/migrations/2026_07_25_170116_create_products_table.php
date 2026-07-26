<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            $table->uuid('uuid')->unique();

            // Relationships
            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            // Pricing
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('compare_at_price', 12, 2)->nullable();

            $table->string('currency', 10)->default('USD');

            // MOQ
            $table->decimal('min_order_quantity', 10, 2)->default(1);
            $table->decimal('max_order_quantity', 10, 2)->nullable();

            // Inventory (temporary)
            $table->integer('stock_quantity')->default(0);

            // Dimensions
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();

            // Flags
            $table->boolean('featured')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('requires_shipping')->default(true);

            // Workflow
            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'published',
                'archived',
            ])->default('draft');

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('published_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('supplier_id');
            $table->index('brand_id');
            $table->index('unit_id');
            $table->index('status');
            $table->index('featured');
            $table->index('sku');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
