<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model: FoodItem
 *
 * Kriteria AHP Final (Indeks 0–2) — Skor Kontrastif:
 *   [0] skor_rasa            — 2.0=Segar | 2.2=Agak Segar | 3.0=Pedas
 *   [1] skor_nutrisi         — 1.2=Karbo | 2.0=Telur | 2.6=Ayam | 3.0=Sapi
 *   [2] skor_jenis_hidangan  — 2.5=Kuah Ringan | 2.8=Kering/Kuah | 3.0=Mutlak
 */
class FoodItem extends Model
{
    protected $table = 'food_items';

    protected $fillable = [
        'nama_menu',
        'harga',
        'skor_rasa',
        'skor_nutrisi',
        'skor_jenis_hidangan',
    ];

    protected $casts = [
        'harga'               => 'integer',
        'skor_rasa'           => 'float',
        'skor_nutrisi'        => 'float',
        'skor_jenis_hidangan' => 'float',
    ];

    /**
     * Array skor AHP — urutan WAJIB sesuai indeks kriteria di AHPService:
     *   [0] = skor_rasa
     *   [1] = skor_nutrisi
     *   [2] = skor_jenis_hidangan
     *
     * @return array<int, float>
     */
    public function getSkorArray(): array
    {
        return [
            (float) $this->skor_rasa,           // [0] Rasa
            (float) $this->skor_nutrisi,         // [1] Nutrisi
            (float) $this->skor_jenis_hidangan,  // [2] Jenis Hidangan
        ];
    }
}
