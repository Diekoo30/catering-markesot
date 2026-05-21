<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AhpCriterion extends Model
{
    protected $fillable = ['name', 'weight', 'type'];

    public function scores()
    {
        return $this->hasMany(AhpAlternativeScore::class, 'ahp_criterion_id');
    }
}
