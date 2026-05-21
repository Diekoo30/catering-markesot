<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kriteria AHP Final: Rasa | Nutrisi | Jenis Hidangan
 * Skala skor 1–3 (SEMUA kolom float, desimal kontrastif):
 *   skor_rasa            : 2.0=Segar | 2.2=Agak Segar | 3.0=Pedas
 *   skor_nutrisi         : 1.2=Karbo/Mi | 2.0=Telur | 2.6=Ayam | 3.0=Daging Sapi
 *   skor_jenis_hidangan  : 2.5=Kuah Ringan | 2.8=Kering/Kuah | 3.0=Mutlak Kering/Kuah
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('food_items');

        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->string('nama_menu');
            $table->unsignedInteger('harga')->default(0);

            $table->float('skor_rasa', 3, 1)
                ->default(1.0)
                ->comment('2.0=Segar | 2.2=Agak Segar | 3.0=Pedas');

            $table->float('skor_nutrisi', 3, 1)
                ->default(1.0)
                ->comment('1.2=Karbo | 2.0=Telur | 2.6=Ayam | 3.0=Sapi');

            $table->float('skor_jenis_hidangan', 3, 1)
                ->default(1.0)
                ->comment('2.5=Kuah Ringan | 2.8=Kering/Kuah | 3.0=Mutlak');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
