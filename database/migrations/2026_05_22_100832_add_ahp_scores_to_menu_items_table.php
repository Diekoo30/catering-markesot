<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Tambah kolom skor AHP ke menu_items agar rekomendasi
     * langsung sinkron dengan menu yang dikelola admin.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->float('skor_rasa')->default(1.0)->after('notes');
            $table->float('skor_nutrisi')->default(1.0)->after('skor_rasa');
            $table->float('skor_jenis_hidangan')->default(1.0)->after('skor_nutrisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['skor_rasa', 'skor_nutrisi', 'skor_jenis_hidangan']);
        });
    }
};
