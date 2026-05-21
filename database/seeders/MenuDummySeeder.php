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
            // Menu dari Foto
            ['category_id' => $catMain, 'name' => 'Nasi Goreng',          'price' => 10000, 'description' => 'Aromatik, gurih pedas, bikin nambah terus!'],
            ['category_id' => $catMain, 'name' => 'Mie Dok Dok Kuah',     'price' => 12000, 'description' => 'Mie kuah hangat khas abang-abang malam'],
            ['category_id' => $catMain, 'name' => 'Mie Dok Dok Goreng',   'price' => 12000, 'description' => 'Mie goreng rumahan, pedas gurih mantap'],
            ['category_id' => $catMain, 'name' => 'Soto Ayam Kampung',    'price' => 12000, 'description' => 'Kuah bening hangat, ayam kampung empuk'],
            ['category_id' => $catMain, 'name' => 'Rawon',                'price' => 13000, 'description' => 'Kuah bening hangat, ayam kampung empuk'],
            
            // Minuman (Harga & deskripsi tidak ada di foto, diisi default)
            ['category_id' => $catDrink, 'name' => 'Es Teh',              'price' => 3000,  'description' => 'Es teh segar'],
            ['category_id' => $catDrink, 'name' => 'Es Jeruk',            'price' => 5000,  'description' => 'Es jeruk segar'],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id'   => $item['category_id'],
                    'price'         => $item['price'],
                    'description'   => $item['description'],
                    'is_available'  => true,
                    'is_featured'   => false,
                ]
            );
        }
    }
}
