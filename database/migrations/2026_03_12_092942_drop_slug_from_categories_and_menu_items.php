<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trik jika menggunakan SQLite (laptop Fachry), lewati fungsi ini agar tidak error
        if (config('database.default') === 'sqlite') {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable();
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable();
        });
    }
};