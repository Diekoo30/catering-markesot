<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('enable_ahp_recommendation')->default(false)->after('sort_order');
        });

        // Set "Makanan Utama" to true by default
        DB::table('categories')
            ->where('name', 'Makanan Utama')
            ->update(['enable_ahp_recommendation' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('enable_ahp_recommendation');
        });
    }
};
