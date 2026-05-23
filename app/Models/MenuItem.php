<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    use SoftDeletes;

    /**
     * Auto-generate slug dari nama menu saat creating/updating.
     * Berlaku untuk Filament admin, seeder, maupun API — tidak perlu input manual.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (MenuItem $item) {
            if (empty($item->slug)) {
                $item->slug = self::generateUniqueSlug($item->name);
            }
        });

        static::updating(function (MenuItem $item) {
            // Update slug jika nama berubah dan slug masih mengikuti nama lama
            if ($item->isDirty('name') && ! $item->isDirty('slug')) {
                $item->slug = self::generateUniqueSlug($item->name, $item->id);
            }
        });
    }

    /**
     * Buat slug unik — tambahkan suffix angka jika sudah ada.
     */
    private static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 1;

        $query = static::withTrashed()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug  = $base . '-' . $i++;
            $query = static::withTrashed()->where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
        }

        return $slug;
    }


    protected $fillable = [
        'category_id','name','slug','description','price',
        'unit','image','is_available','is_featured','min_order_qty','notes',
        // Skor kriteria AHP (skala 1.0–3.0)
        'skor_rasa',
        'skor_nutrisi',
        'skor_jenis_hidangan',
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
