<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Update food_items ke 3 Kriteria AHP
 *
 * Sesuai jurnal Ricky Isnawan (UKDW, 2021), kriteria SPK
 * pemilihan menu makanan dipangkas menjadi 3 kriteria utama:
 *   [0] Rasa    — Cita rasa & kelezatan hidangan
 *   [1] Nutrisi — Kandungan gizi & protein
 *   [2] Porsi   — Ukuran sajian & kemampuan mengenyangkan
 *
 * Kriteria Harga sengaja TIDAK dimasukkan karena sudah
 * diakomodasi oleh filter standar aplikasi.
 *
 * Skala Skor (1–3):
 *   skor_rasa    : 2=Gurih Segar  | 3=Pedas Mantap
 *   skor_nutrisi : 2=Standar/Telur| 3=Tinggi/Ayam/Sapi
 *   skor_porsi   : 2=Sedang       | 3=Besar/Kenyang
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop tabel lama (4-kriteria) dan buat ulang dengan skema 3-kriteria
        // Pendekatan ini paling aman untuk kompatibilitas SQLite & MySQL
        Schema::dropIfExists('food_items');

        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->unsignedInteger('harga')->default(0)->comment('Harga dalam rupiah');

            // ── Skor AHP 3 Kriteria (skala 1–3) ──────────────────────
            // Semakin tinggi skor = semakin cocok untuk kriteria tersebut

            $table->tinyInteger('skor_rasa')
                ->default(1)
                ->comment('1=Ringan | 2=Gurih Segar | 3=Pedas Mantap');

            $table->tinyInteger('skor_nutrisi')
                ->default(1)
                ->comment('1=Rendah | 2=Standar/Telur | 3=Tinggi/Ayam/Sapi');

            $table->tinyInteger('skor_porsi')
                ->default(1)
                ->comment('1=Kecil | 2=Sedang | 3=Besar/Kenyang');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
