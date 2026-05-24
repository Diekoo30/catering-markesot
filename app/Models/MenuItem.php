<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "category_id","name","description","price",
        "image","is_available","is_featured","notes",
        "skor_rasa",
        "skor_nutrisi",
        "skor_jenis_hidangan",
    ];

    protected $casts = [
        "price"               => "decimal:2",
        "is_available"        => "boolean",
        "is_featured"         => "boolean",
        "skor_rasa"           => "float",
        "skor_nutrisi"        => "float",
        "skor_jenis_hidangan" => "float",
    ];



    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Array skor AHP — urutan WAJIB sesuai indeks kriteria di AHPService:
     *   [0] = skor_rasa           (1.0=Tidak Pedas | 2.0=Agak Pedas | 3.0=Pedas)
     *   [1] = skor_nutrisi        (1.0=Karbo | 2.0=Telur | 2.6=Ayam | 3.0=Sapi)
     *   [2] = skor_jenis_hidangan (1.5=Kering | 2.5=Kuah Ringan | 3.0=Kuah Kaya)
     *
     * @return array<int, float>
     */
    public function getSkorArray(): array
    {
        return [
            (float) ($this->skor_rasa ?? 1.5),           // [0] Rasa
            (float) ($this->skor_nutrisi ?? 1.5),         // [1] Nutrisi
            (float) ($this->skor_jenis_hidangan ?? 1.5),  // [2] Jenis Hidangan
        ];
    }
}
