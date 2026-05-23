<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Hapus tabel food_items
 *
 * Tabel food_items sudah tidak diperlukan karena skor AHP
 * sekarang disimpan langsung di tabel menu_items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('food_items');
    }

    public function down(): void
    {
        // Tidak perlu recreate — sudah digantikan oleh kolom di menu_items
    }
};
