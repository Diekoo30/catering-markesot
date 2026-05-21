<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tabel food_items
 *
 * Tabel ini menyimpan data menu makanan Markesot beserta
 * skor kriteria AHP yang sudah ternormalisasi pada skala 1-3.
 *
 * Skala Skor:
 *   - Harga      : 1 = Mahal (>=13k), 2 = Sedang (11k-12k), 3 = Murah (<=10k)
 *   - Jenis      : 1 = Kering/Goreng,  2 = Kuah/Hangat
 *   - Nutrisi    : 1 = Protein Telur/Campuran, 2 = Protein Ayam, 3 = Protein Sapi
 *   - Rasa       : 1 = Gurih Segar/Bening, 2 = Gurih Pedas
 *
 * Penggunaan integer (tinyInteger) memastikan kompatibilitas
 * antara SQLite dan MySQL tanpa raw SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();

            // Nama menu makanan
            $table->string('nama_menu');

            // Harga asli dalam Rupiah (untuk display)
            $table->integer('harga');

            // --- KOLOM SKOR AHP (skala 1-3) ---

            /**
             * Skor Harga (Cost Criterion → dibalik agar "murah = lebih baik")
             * 3 = Murah (<=10.000)
             * 2 = Sedang (11.000-12.000)
             * 1 = Mahal (>=13.000)
             */
            $table->tinyInteger('skor_harga')->default(1);

            /**
             * Skor Jenis Hidangan (Benefit Criterion)
             * 2 = Kuah/Hangat (lebih mengenyangkan & cocok semua kondisi)
             * 1 = Kering/Goreng
             */
            $table->tinyInteger('skor_jenis_hidangan')->default(1);

            /**
             * Skor Kandungan Nutrisi (Benefit Criterion)
             * 3 = Protein Sapi (nilai gizi tertinggi)
             * 2 = Protein Ayam
             * 1 = Protein Telur/Campuran
             */
            $table->tinyInteger('skor_kandungan_nutrisi')->default(1);

            /**
             * Skor Rasa Dominan (Benefit Criterion)
             * 2 = Gurih Pedas (lebih diminati mahasiswa)
             * 1 = Gurih Segar/Bening
             */
            $table->tinyInteger('skor_rasa_dominan')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
