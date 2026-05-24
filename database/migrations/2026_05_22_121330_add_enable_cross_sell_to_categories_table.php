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
            $table->boolean('enable_cross_sell')->default(false)->after('enable_ahp_recommendation');
        });

        // Set "Makanan Ringan" and "Minuman" to true by default for cross-selling
        DB::table('categories')
            ->whereIn('name', ['Makanan Ringan', 'Minuman'])
            ->update(['enable_cross_sell' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('enable_cross_sell');
        });
    }
};
