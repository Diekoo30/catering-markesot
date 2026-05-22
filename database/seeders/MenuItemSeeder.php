<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * MenuItemSeeder — Data menu asli Kantin Markesot Universitas Jember
 *
 * Skor AHP per menu (skala 1.0–3.0):
 *   skor_rasa           : 1.0=Tidak Pedas | 1.4=Agak Gurih | 2.5=Pedas Gurih | 3.0=Pedas Kuat
 *   skor_nutrisi        : 1.0=Dominan Karbo | 2.2=Telur+Sayur | 2.4=Ayam | 3.0=Sapi
 *   skor_jenis_hidangan : 1.0=Kuah Ringan | 1.5=Goreng Standar | 2.0=Kuah Rempah | 2.4=Kuah Hangat | 3.0=Kering Mutlak
 *
 * Strategi skor (dikalibrasi: semua 27 kombinasi diuji, setiap menu bisa #1):
 *   Nasi Goreng        → RAJA KERING (jenis=3.0) — menang saat user prioritas jenis hidangan
 *   Mie Dok Dok Kuah   → BALANCED KUAH (nutrisi=2.2, jenis=2.4) — menang saat nutrisi+jenis seimbang
 *   Mie Dok Dok Goreng → RAJA PEDAS (rasa=3.0) — menang saat user prioritas rasa
 *   Soto Ayam Kampung  → COMBO RASA+GIZI (rasa=2.5, nutrisi=2.4) — menang saat rasa+nutrisi
 *   Rawon              → RAJA GIZI (nutrisi=3.0) — menang saat user prioritas nutrisi
 */
class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $catMain  = Category::where('name', 'Makanan Utama')->first()?->id;
        $catDrink = Category::where('name', 'Minuman')->first()?->id;

        if (!$catMain) {
            $this->command->warn('⚠️  Kategori belum tersedia. Jalankan CategorySeeder terlebih dahulu.');
            return;
        }

        $items = [
            // ═══ MAKANAN UTAMA — 5 Menu Andalan Markesot ═══
            // Skor telah dikalibrasi agar setiap menu bisa menjadi #1 (27 kombinasi diuji)
            [
                'category_id'         => $catMain,
                'name'                => 'Nasi Goreng',
                'price'               => 10000,
                'is_featured'         => true,
                'description'         => 'Aromatik, gurih pedas, bikin nambah terus!',
                'skor_rasa'           => 1.2,  // Rasa: gurih ringan, tidak pedas
                'skor_nutrisi'        => 1.0,  // Nutrisi: dominan karbo
                'skor_jenis_hidangan' => 3.0,  // Jenis: RAJA KERING — menang saat user prioritas jenis hidangan
                'notes'               => [
                    'emoji'   => '🍳',
                    'harga'   => 5,
                    'rasa'    => 4,
                    'sehat'   => 3,
                    'kenyang' => 4,
                    'tags'    => ['Gurih', 'Favorit', 'Ekonomis'],
                ],
            ],
            [
                'category_id'         => $catMain,
                'name'                => 'Mie Dok Dok Kuah',
                'price'               => 12000,
                'is_featured'         => true,
                'description'         => 'Mie kuah hangat khas abang-abang malam.',
                'skor_rasa'           => 1.4,  // Rasa: kuah gurih, sedikit pedas
                'skor_nutrisi'        => 2.2,  // Nutrisi: kuah bergizi (telur+sayur)
                'skor_jenis_hidangan' => 2.4,  // Jenis: kuah hangat, skor tinggi
                'notes'               => [
                    'emoji'   => '🍜',
                    'harga'   => 4,
                    'rasa'    => 4,
                    'sehat'   => 4,
                    'kenyang' => 4,
                    'tags'    => ['Hangat', 'Pedas', 'Nyemek'],
                ],
            ],
            [
                'category_id'         => $catMain,
                'name'                => 'Mie Dok Dok Goreng',
                'price'               => 12000,
                'is_featured'         => true,
                'description'         => 'Mie goreng rumahan, pedas gurih mantap.',
                'skor_rasa'           => 3.0,  // Rasa: RAJA PEDAS — menang saat user prioritas rasa
                'skor_nutrisi'        => 1.0,  // Nutrisi: dominan karbo
                'skor_jenis_hidangan' => 1.5,  // Jenis: goreng standar
                'notes'               => [
                    'emoji'   => '🍝',
                    'harga'   => 4,
                    'rasa'    => 5,
                    'sehat'   => 3,
                    'kenyang' => 4,
                    'tags'    => ['Pedas Mantap', 'Bumbu Lekat', 'Gurih'],
                ],
            ],
            [
                'category_id'         => $catMain,
                'name'                => 'Soto Ayam Kampung',
                'price'               => 12000,
                'is_featured'         => true,
                'description'         => 'Kuah bening hangat, ayam kampung empuk.',
                'skor_rasa'           => 2.5,  // Rasa: gurih pedas khas soto
                'skor_nutrisi'        => 2.4,  // Nutrisi: ayam kampung, cukup bergizi
                'skor_jenis_hidangan' => 1.0,  // Jenis: kuah bening ringan
                'notes'               => [
                    'emoji'   => '🥣',
                    'harga'   => 4,
                    'rasa'    => 4,
                    'sehat'   => 5,
                    'kenyang' => 4,
                    'tags'    => ['Kuah Segar', 'Ayam Kampung', 'Bergizi'],
                ],
            ],
            [
                'category_id'         => $catMain,
                'name'                => 'Rawon',
                'price'               => 13000,
                'is_featured'         => true,
                'description'         => 'Kuah hitam kluwek khas Jawa Timur, daging sapi empuk, tauge, dan telur asin.',
                'skor_rasa'           => 1.0,  // Rasa: gurih khas kluwek, tidak pedas
                'skor_nutrisi'        => 3.0,  // Nutrisi: RAJA GIZI (sapi) — menang saat user prioritas nutrisi
                'skor_jenis_hidangan' => 2.0,  // Jenis: kuah kaya rempah
                'notes'               => [
                    'emoji'   => '🥩',
                    'harga'   => 4,
                    'rasa'    => 5,
                    'sehat'   => 5,
                    'kenyang' => 5,
                    'tags'    => ['Daging Sapi', 'Khas Jatim', 'Kluwek Pekat'],
                ],
            ],
        ];

        // ═══ MINUMAN (jika kategori tersedia) ═══
        if ($catDrink) {
            $drinks = [
                [
                    'category_id'         => $catDrink,
                    'name'                => 'Es Teh',
                    'price'               => 3000,
                    'is_featured'         => false,
                    'description'         => 'Teh manis dingin segar, pelepas dahaga andalan mahasiswa.',
                    'skor_rasa'           => 1.0,
                    'skor_nutrisi'        => 1.0,
                    'skor_jenis_hidangan' => 1.0,
                    'notes'               => [
                        'emoji'   => '🍹',
                        'harga'   => 5,
                        'rasa'    => 3,
                        'sehat'   => 3,
                        'kenyang' => 2,
                        'tags'    => ['Dingin', 'Segar', 'Manis'],
                    ],
                ],
                [
                    'category_id'         => $catDrink,
                    'name'                => 'Es Jeruk',
                    'price'               => 5000,
                    'is_featured'         => false,
                    'description'         => 'Jeruk peras segar dengan es batu, manis alami.',
                    'skor_rasa'           => 1.0,
                    'skor_nutrisi'        => 1.2,
                    'skor_jenis_hidangan' => 1.0,
                    'notes'               => [
                        'emoji'   => '🍊',
                        'harga'   => 5,
                        'rasa'    => 4,
                        'sehat'   => 4,
                        'kenyang' => 2,
                        'tags'    => ['Jeruk Peras', 'Kaya Vit C', 'Segar'],
                    ],
                ],
            ];
            $items = array_merge($items, $drinks);
        }

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id'         => $item['category_id'],
                    'price'               => $item['price'],
                    'description'         => $item['description'],
                    'unit'                => 'porsi',
                    'is_available'        => true,
                    'is_featured'         => $item['is_featured'] ?? false,
                    'min_order_qty'       => 1,
                    'skor_rasa'           => $item['skor_rasa']           ?? 1.5,
                    'skor_nutrisi'        => $item['skor_nutrisi']        ?? 1.5,
                    'skor_jenis_hidangan' => $item['skor_jenis_hidangan'] ?? 1.5,
                    'notes'               => isset($item['notes']) ? json_encode($item['notes']) : null,
                ]
            );
        }

        $this->command->info('MenuItemSeeder: ' . count($items) . ' menu Markesot berhasil ditambahkan.');
    }
}
