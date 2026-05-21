<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AhpAlternativeScore extends Model
{
    protected $fillable = ['menu_item_id', 'ahp_criterion_id', 'score'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function criterion()
    {
        return $this->belongsTo(AhpCriterion::class, 'ahp_criterion_id');
    }
}
