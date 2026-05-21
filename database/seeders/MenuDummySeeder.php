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
            ['category_id' => $catMain, 'name' => 'Nasi Goreng',          'price' => 10000, 'is_featured' => true,  'description' => 'Nasi goreng spesial dengan telur mata sapi, irisan ayam, kerupuk, dan acar segar.'],
            ['category_id' => $catMain, 'name' => 'Mie Dok Dok Goreng',   'price' => 12000, 'is_featured' => true,  'description' => 'Mie goreng khas Markesot dengan bumbu pedas spesial, telur, dan sayuran.'],
            ['category_id' => $catMain, 'name' => 'Mie Dok Dok Kuah',     'price' => 12000, 'is_featured' => true,  'description' => 'Mie kuah pedas Markesot dengan kaldu gurih, telur rebus, dan sayuran segar.'],
            ['category_id' => $catMain, 'name' => 'Soto Ayam Kampung',    'price' => 12000, 'is_featured' => true,  'description' => 'Soto ayam kampung asli dengan kuah bening rempah, suwiran ayam, dan bihun.'],
            ['category_id' => $catMain, 'name' => 'Nasi Rawon',           'price' => 13000, 'is_featured' => true,  'description' => 'Rawon daging sapi dengan kuah hitam kluwek, tauge, telur asin, dan sambal.'],
        ];

        // ═══ MINUMAN (jika kategori tersedia) ═══
        if ($catDrink) {
            $drinks = [
                ['category_id' => $catDrink, 'name' => 'Es Teh Manis',    'price' => 3000, 'is_featured' => false, 'description' => 'Teh manis dingin segar, pelepas dahaga andalan mahasiswa.'],
                ['category_id' => $catDrink, 'name' => 'Es Jeruk',        'price' => 5000, 'is_featured' => false, 'description' => 'Jeruk peras segar dengan es batu, manis alami.'],
                ['category_id' => $catDrink, 'name' => 'Teh Hangat',      'price' => 2000, 'is_featured' => false, 'description' => 'Teh hangat manis, cocok untuk menemani makan siang.'],
            ];
            $items = array_merge($items, $drinks);
        }

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id'   => $item['category_id'],
                    'slug'          => Str::slug($item['name']),
                    'price'         => $item['price'],
                    'description'   => $item['description'],
                    'unit'          => 'porsi',
                    'is_available'  => true,
                    'is_featured'   => $item['is_featured'] ?? false,
                    'min_order_qty' => 1,
                ]
            );
        }

        $this->command->info('MenuDummySeeder: ' . count($items) . ' menu Markesot berhasil ditambahkan.');
    }
}
