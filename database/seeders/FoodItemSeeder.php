<?php

namespace Database\Seeders;

use App\Models\FoodItem;
use Illuminate\Database\Seeder;

/**
 * FoodItemSeeder — Skor Penyeimbang Matriks Dinamis (V5 - Hasil Simulasi Presisi)
 *
 * STRATEGI: Mengamankan performa kompetitif agar kelima menu bisa menang bergantian 
 * secara presisi berdasarkan variasi bobot kuesioner tanpa efek saling jegal.
 *
 * ┌──────────────────────┬───────┬─────────┬────────┬───────────────────────┐
 * │ Menu                 │ Rasa  │ Nutrisi │ Jenis  │ Kapan Menang          │
 * ├──────────────────────┼───────┼─────────┼────────┼───────────────────────┤
 * │ Nasi Goreng          │ 1.8   │ 1.2     │ 3.0    │ Jenis >> Rasa/Nutrisi │
 * │ Mie Dok Dok Goreng   │ 3.0   │ 1.0     │ 1.5    │ Rasa >> Nutrisi/Jenis │
 * │ Mie Dok Dok Kuah     │ 1.5   │ 1.5     │ 3.0    │ Jenis + Nutrisi Combo │
 * │ Soto Ayam Kampung    │ 2.3   │ 2.5     │ 1.5    │ Nutrisi + Rasa Combo  │
 * │ Nasi Rawon           │ 1.2   │ 3.0     │ 2.0    │ Nutrisi >> Rasa/Jenis │
 * └──────────────────────┴───────┴─────────┴────────┴───────────────────────┘
 */
class FoodItemSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama terlebih dahulu
        FoodItem::query()->delete();

        $menus = [
            [
                'nama_menu'           => 'Nasi Goreng',
                'harga'               => 10000,
                'skor_rasa'           => 1.4,  
                'skor_nutrisi'        => 1.2,
                'skor_jenis_hidangan' => 3.0,  // Mutlak Unggul Kering (TES 1)
            ],
            [
                'nama_menu'           => 'Mie Dok Dok Goreng',
                'harga'               => 12000,
                'skor_rasa'           => 3.0,  // Mutlak Unggul Rasa Kering (TES 5)
                'skor_nutrisi'        => 1.0,  
                'skor_jenis_hidangan' => 1.5,  
            ],
            [
                'nama_menu'           => 'Mie Dok Dok Kuah',
                'harga'               => 12000,
                'skor_rasa'           => 1.5,
                'skor_nutrisi'        => 1.6,
                'skor_jenis_hidangan' => 3.0,  // Mutlak Unggul Kuah (TES 4)
            ],
            [
                'nama_menu'           => 'Soto Ayam Kampung',
                'harga'               => 12000,
                'skor_rasa'           => 2.3,  // Disesuaikan agar tidak menjegal Mie Goreng di TES 5 dan Rawon di TES 2
                'skor_nutrisi'        => 2.5,  // Kombinasi seimbang Rasa + Gizi (TES 3)
                'skor_jenis_hidangan' => 1.5,  
            ],
            [
                'nama_menu'           => 'Nasi Rawon',
                'harga'               => 13000,
                'skor_rasa'           => 1.2,
                'skor_nutrisi'        => 3.0,  // Mutlak Unggul Gizi (TES 2)
                'skor_jenis_hidangan' => 2.0,  // Diberi nilai tengah proporsional
            ],
        ];

        foreach ($menus as $menu) {
            FoodItem::create($menu);
        }

        $this->command->info('FoodItemSeeder: Update data pelindung skor ekstrem berhasil diterapkan.');
    }
}