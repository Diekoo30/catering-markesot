<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // Baris baru: Ditambahkan agar fungsi Str::slug() bisa terbaca

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Utama',  'description' => 'Hidangan utama seperti nasi, lauk pauk, dan sajian pokok',       'sort_order' => 1],
            ['name' => 'Makanan Ringan', 'description' => 'Snack, camilan, dan kudapan pelengkap',                           'sort_order' => 2],
            ['name' => 'Minuman',        'description' => 'Berbagai pilihan minuman segar dan hangat',                       'sort_order' => 3],
            ['name' => 'Dessert',        'description' => 'Hidangan penutup seperti kue, puding, dan buah-buahan',           'sort_order' => 4],
            ['name' => 'Paket Catering', 'description' => 'Paket lengkap catering untuk berbagai acara dan jumlah porsi',   'sort_order' => 5],
        ];

        // --- BAGIAN INI YANG DIUBAH TOTAL ---
        foreach ($categories as $cat) {
            // Cari data kategori berdasarkan nama, jika tidak ada maka buat instansiasi objek baru
            $category = Category::where('name', $cat['name'])->first() ?: new Category();
            
            // Set nilai properti satu per satu agar lolos dari proteksi Mass Assignment / fillable
            $category->name = $cat['name'];
            $category->description = $cat['description'];
            $category->sort_order = $cat['sort_order'];
            $category->enable_ahp_recommendation = ($cat['name'] === 'Makanan Utama');
            $category->enable_cross_sell = in_array($cat['name'], ['Makanan Ringan', 'Minuman']);
            $category->is_active = true;
            
            if (\Illuminate\Support\Facades\Schema::hasColumn('categories', 'slug')) {
                $category->slug = \Illuminate\Support\Str::slug($cat['name']);
            }
            
            // Simpan perubahan ke database
            $category->save();
        }
        // ------------------------------------
    }
}