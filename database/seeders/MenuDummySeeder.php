<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * MenuDummySeeder — Data Menu Markesot (sesuai menu asli kantin)
 */
class MenuDummySeeder extends Seeder
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
                'category_id' => $catMain, 
                'name' => 'Nasi Goreng',          
                'price' => 10000, 
                'is_featured' => true,  
                'description' => 'Nasi goreng spesial dengan telur mata sapi, irisan ayam, kerupuk, dan acar segar.',
                'skor_rasa' => 1.2,
                'skor_nutrisi' => 1.0,
                'skor_jenis_hidangan' => 3.0,
                'notes' => [
                    'emoji'   => '🍳',
                    'harga'   => 5,
                    'rasa'    => 4,
                    'sehat'   => 3,
                    'kenyang' => 4,
                    'tags'    => ['Gurih', 'Favorit', 'Ekonomis'],
                ],
            ],
            [
                'category_id' => $catMain, 
                'name' => 'Mie Dok Dok Goreng',   
                'price' => 12000, 
                'is_featured' => true,  
                'description' => 'Mie goreng khas Markesot dengan bumbu pedas spesial, telur, dan sayuran.',
                'skor_rasa' => 3.0,
                'skor_nutrisi' => 1.0,
                'skor_jenis_hidangan' => 1.5,
                'notes' => [
                    'emoji'   => '🍝',
                    'harga'   => 4,
                    'rasa'    => 5,
                    'sehat'   => 3,
                    'kenyang' => 4,
                    'tags'    => ['Pedas Mantap', 'Bumbu Lekat', 'Gurih'],
                ],
            ],
            [
                'category_id' => $catMain, 
                'name' => 'Mie Dok Dok Kuah',     
                'price' => 12000, 
                'is_featured' => true,  
                'description' => 'Mie kuah pedas Markesot dengan kaldu gurih, telur rebus, dan sayuran segar.',
                'skor_rasa' => 1.4,
                'skor_nutrisi' => 2.2,
                'skor_jenis_hidangan' => 2.4,
                'notes' => [
                    'emoji'   => '🍜',
                    'harga'   => 4,
                    'rasa'    => 4,
                    'sehat'   => 4,
                    'kenyang' => 4,
                    'tags'    => ['Hangat', 'Pedas', 'Nyemek'],
                ],
            ],
            [
                'category_id' => $catMain, 
                'name' => 'Soto Ayam Kampung',    
                'price' => 12000, 
                'is_featured' => true,  
                'description' => 'Soto ayam kampung asli dengan kuah bening rempah, suwiran ayam, dan bihun.',
                'skor_rasa' => 2.5,
                'skor_nutrisi' => 2.4,
                'skor_jenis_hidangan' => 1.0,
                'notes' => [
                    'emoji'   => '🥣',
                    'harga'   => 4,
                    'rasa'    => 4,
                    'sehat'   => 5,
                    'kenyang' => 4,
                    'tags'    => ['Kuah Segar', 'Ayam Kampung', 'Bergizi'],
                ],
            ],
            [
                'category_id' => $catMain, 
                'name' => 'Rawon',           
                'price' => 13000, 
                'is_featured' => true,  
                'description' => 'Rawon daging sapi dengan kuah hitam kluwek, tauge, telur asin, dan sambal.',
                'skor_rasa' => 1.0,
                'skor_nutrisi' => 3.0,
                'skor_jenis_hidangan' => 2.0,
                'notes' => [
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
                    'category_id' => $catDrink, 
                    'name' => 'Es Teh',    
                    'price' => 3000, 
                    'is_featured' => false, 
                    'description' => 'Teh manis dingin segar, pelepas dahaga andalan mahasiswa.',
                    'skor_rasa' => 1.0,
                    'skor_nutrisi' => 1.0,
                    'skor_jenis_hidangan' => 1.0,
                    'notes' => [
                        'emoji'   => '🍹',
                        'harga'   => 5,
                        'rasa'    => 3,
                        'sehat'   => 3,
                        'kenyang' => 2,
                        'tags'    => ['Dingin', 'Segar', 'Manis'],
                    ],
                ],
                [
                    'category_id' => $catDrink, 
                    'name' => 'Es Jeruk',        
                    'price' => 5000, 
                    'is_featured' => false, 
                    'description' => 'Jeruk peras segar dengan es batu, manis alami.',
                    'skor_rasa' => 1.0,
                    'skor_nutrisi' => 1.2,
                    'skor_jenis_hidangan' => 1.0,
                    'notes' => [
                        'emoji'   => '🍊',
                        'harga'   => 5,
                        'rasa'    => 4,
                        'sehat'   => 4,
                        'kenyang' => 2,
                        'tags'    => ['Jeruk Peras', 'Kaya Vit C', 'Segar'],
                    ],
                ],
                [
                    'category_id' => $catDrink, 
                    'name' => 'Teh Hangat',      
                    'price' => 2000, 
                    'is_featured' => false, 
                    'description' => 'Teh hangat manis, cocok untuk menemani makan siang.',
                    'skor_rasa' => 1.0,
                    'skor_nutrisi' => 1.0,
                    'skor_jenis_hidangan' => 1.0,
                    'notes' => [
                        'emoji'   => '🍵',
                        'harga'   => 5,
                        'rasa'    => 3,
                        'sehat'   => 3,
                        'kenyang' => 2,
                        'tags'    => ['Hangat', 'Menenangkan'],
                    ],
                ],
            ];
            $items = array_merge($items, $drinks);
        }

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id'   => $item['category_id'],
                    'price'         => $item['price'],
                    'description'   => $item['description'],
                    'unit'          => 'porsi',
                    'is_available'  => true,
                    'is_featured'   => $item['is_featured'] ?? false,
                    'min_order_qty' => 1,
                    'skor_rasa'           => $item['skor_rasa']           ?? 1.5,
                    'skor_nutrisi'        => $item['skor_nutrisi']        ?? 1.5,
                    'skor_jenis_hidangan' => $item['skor_jenis_hidangan'] ?? 1.5,
                    'notes'               => isset($item['notes']) ? json_encode($item['notes']) : null,
                ]
            );
        }

        $this->command->info('MenuDummySeeder: ' . count($items) . ' menu Markesot berhasil ditambahkan.');
    }
}
