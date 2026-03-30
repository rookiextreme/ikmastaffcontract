<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceCompetencyItem extends Model
{
    protected $fillable = [
        'code','name','description','weight','sort_order','is_active'
    ];

    public function scores()
    {
        return $this->hasMany(PerformanceCompetencyScore::class, 'competency_item_id');
    }
}
