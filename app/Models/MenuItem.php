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
        'category_id','name','slug','description','price',
        'unit','image','is_available','is_featured','min_order_qty','notes',
        'skor_rasa', 'skor_nutrisi', 'skor_jenis_hidangan',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'is_available'        => 'boolean',
        'is_featured'         => 'boolean',
        'min_order_qty'       => 'integer',
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
            (float) ($this->skor_rasa ?? 1.0),
            (float) ($this->skor_nutrisi ?? 1.0),
            (float) ($this->skor_jenis_hidangan ?? 1.0),
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ahpScores(): HasMany
    {
        return $this->hasMany(AhpAlternativeScore::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

}
