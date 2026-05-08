<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuDummySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil ID kategori berdasarkan nama
        $catMain    = Category::where('name', 'Makanan Utama')->first()?->id;
        $catSnack   = Category::where('name', 'Makanan Ringan')->first()?->id;
        $catDrink   = Category::where('name', 'Minuman')->first()?->id;

        if (!$catMain || !$catSnack || !$catDrink) {
            $this->command->warn('Kategori belum tersedia. Jalankan CategorySeeder terlebih dahulu.');
            return;
        }

        $items = [
            // Makanan Utama
            ['category_id' => $catMain, 'name' => 'Nasi Goreng Spesial',   'price' => 15000, 'description' => 'Nasi goreng dengan telur mata sapi, ayam suwir, dan kerupuk renyah.'],
            ['category_id' => $catMain, 'name' => 'Mie Goreng Jawa',       'price' => 13000, 'description' => 'Mie goreng khas Jawa dengan bumbu kecap manis dan sayuran segar.'],
            ['category_id' => $catMain, 'name' => 'Ayam Geprek Sambal',    'price' => 18000, 'description' => 'Ayam geprek crispy dengan sambal bawang merah yang pedas menggoda.'],
            ['category_id' => $catMain, 'name' => 'Soto Ayam Lamongan',    'price' => 14000, 'description' => 'Kuah bening kaya rempah dengan suwiran ayam dan bihun.'],
            ['category_id' => $catMain, 'name' => 'Nasi Rendang Padang',   'price' => 20000, 'description' => 'Rendang sapi empuk dengan santan kental dan bumbu rempah pilihan.'],
            ['category_id' => $catMain, 'name' => 'Bakso Urat Jumbo',      'price' => 16000, 'description' => 'Bakso daging sapi besar isi urat kenyal dalam kuah kaldu hangat.'],
            ['category_id' => $catMain, 'name' => 'Rawon Surabaya',        'price' => 19000, 'description' => 'Sup daging sapi hitam khas Surabaya dengan kluwek asli.'],

            // Makanan Ringan
            ['category_id' => $catSnack, 'name' => 'Tahu Crispy Pedas',    'price' => 8000,  'description' => 'Tahu goreng garing dengan taburan cabai dan bumbu rempah.'],
            ['category_id' => $catSnack, 'name' => 'Pisang Goreng Keju',   'price' => 10000, 'description' => 'Pisang goreng renyah dengan lelehan keju mozzarella.'],
            ['category_id' => $catSnack, 'name' => 'Dimsum Ayam',          'price' => 12000, 'description' => 'Dimsum kukus isi ayam cincang dengan saus sambal kecap.'],

            // Minuman
            ['category_id' => $catDrink, 'name' => 'Es Teh Manis',        'price' => 5000,  'description' => 'Teh manis dingin segar khas warung kampus.'],
            ['category_id' => $catDrink, 'name' => 'Es Jeruk Peras',      'price' => 7000,  'description' => 'Jeruk peras segar langsung dari buah asli.'],
            ['category_id' => $catDrink, 'name' => 'Kopi Susu Gula Aren', 'price' => 12000, 'description' => 'Espresso shot dengan susu segar dan gula aren pilihan.'],
            ['category_id' => $catDrink, 'name' => 'Jus Alpukat',         'price' => 10000, 'description' => 'Alpukat blended lembut dengan susu coklat dan madu.'],
            ['category_id' => $catDrink, 'name' => 'Es Cendol Dawet',     'price' => 8000,  'description' => 'Cendol pandan dengan santan kelapa dan gula merah.'],
            ['category_id' => $catDrink, 'name' => 'Lemon Tea',           'price' => 8000,  'description' => 'Teh hitam segar dengan perasan lemon dan madu alami.'],
            ['category_id' => $catDrink, 'name' => 'Matcha Latte',        'price' => 14000, 'description' => 'Green tea matcha premium dengan susu segar.'],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id'   => $item['category_id'],
                    'price'         => $item['price'],
                    'description'   => $item['description'],
                    'unit'          => 'porsi',
                    'is_available'  => true,
                    'is_featured'   => false,
                    'min_order_qty' => 1,
                ]
            );
        }
    }
}
