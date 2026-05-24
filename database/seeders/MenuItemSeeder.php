<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * MenuItemSeeder — Data menu asli Kantin Markesot Universitas Jember
 *
 * Skor AHP per menu (skala 1.0–3.0):
 *   skor_rasa           : 1.0=Segar/Tidak Pedas | 2.0=Agak Pedas | 3.0=Pedas Kuat
 *   skor_nutrisi        : 1.0=Karbo | 2.0=Ada Telur | 2.5=Ada Ayam | 3.0=Ada Sapi
 *   skor_jenis_hidangan : 1.5=Goreng/Kering | 2.5=Kuah Ringan | 3.0=Kuah Kaya/Berkuah Pekat
 *
 * Strategi skor — setiap menu bisa unggul di skenario berbeda:
 *   Nasi Goreng        → menang jika Rasa & Kering dominan (R-N: kiri, R-J: kiri, N-J: sama/kiri)
 *   Mie Dok Dok Kuah   → menang jika Kuah sangat dominan (R-J: kanan, N-J: kanan)
 *   Mie Dok Dok Goreng → menang jika Rasa pedas sangat dominan & Kering (R-N: kiri, R-J: kiri, N-J: kanan)
 *   Soto Ayam Kampung  → menang jika nutrisi ayam + kuah ringan seimbang (Skenario standar/balanced)
 *   Rawon              → menang jika Nutrisi sapi sangat dominan (R-N: kanan, N-J: kiri)
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
            [
                'category_id'         => $catMain,
                'name'                => 'Nasi Goreng',
                'price'               => 10000,
                'is_featured'         => true,
                'description'         => 'Aromatik, gurih pedas, bikin nambah terus!',
                'skor_rasa'           => 2.5,  // Rasa: Gurih, rasa pedas & rempah harum seimbang
                'skor_nutrisi'        => 1.6,  // Nutrisi: Karbo nasi dengan telur, gizi sedang-rendah
                'skor_jenis_hidangan' => 1.8,  // Jenis: Hidangan kering/goreng terpopuler (skala 1.5-1.8)
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
                'skor_rasa'           => 1.5,  // Rasa: Agak pedas gurih sedang, kuah melarutkan pedasnya
                'skor_nutrisi'        => 1.5,  // Nutrisi: Karbo mie instan dengan tambahan telur dan sawi
                'skor_jenis_hidangan' => 3.0,  // Jenis: Mutlak Unggul Kuah (kuah pekat hangat melimpah)
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
                'skor_rasa'           => 2.8,  // Rasa: Mutlak Unggul Rasa (pedas mantap & gurih pekat meresap)
                'skor_nutrisi'        => 1.0,  // Nutrisi: Karbohidrat dominan mie instan goreng
                'skor_jenis_hidangan' => 1.5,  // Jenis: Hidangan kering/goreng
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
                'skor_rasa'           => 1.8,  // Rasa: Gurih segar alami kaldu ayam, tidak pedas
                'skor_nutrisi'        => 2.2,  // Nutrisi: Protein tinggi & sehat dari suwiran daging ayam kampung
                'skor_jenis_hidangan' => 2.5,  // Jenis: Hidangan kuah ringan menyegarkan
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
                'skor_rasa'           => 1.0,  // Rasa: Gurih khas kluwek, tidak pedas sama sekali
                'skor_nutrisi'        => 3.0,  // Nutrisi: Mutlak Unggul Gizi (protein tinggi & zat besi dari daging sapi, tauge, telur asin)
                'skor_jenis_hidangan' => 2.0,  // Jenis: Hidangan berkuah pekat kluwek
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
                    'is_available'        => true,
                    'is_featured'         => $item['is_featured'] ?? false,
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
