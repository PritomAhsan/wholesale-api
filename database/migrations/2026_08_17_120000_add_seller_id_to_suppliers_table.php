<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('seller_id')->nullable()->unique()->after('uuid');
        });

        DB::table('suppliers')->orderBy('id')->select('id')->chunkById(100, function ($suppliers) {

            foreach ($suppliers as $supplier) {

                do {

                    $sellerId = 'BLK-' . strtoupper(Str::random(6));

                } while (DB::table('suppliers')->where('seller_id', $sellerId)->exists());

                DB::table('suppliers')
                    ->where('id', $supplier->id)
                    ->update(['seller_id' => $sellerId]);

            }

        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('seller_id');
        });
    }
};
