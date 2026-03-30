<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceCompetencyScore extends Model
{
    protected $table = 'performance_competency_scores';

    protected $fillable = [
        'evaluation_id',
        'competency_item_id',

        // ✅ Kolum baru (PPP/PPK)
        'ppp_score',
        'ppp_comment',
        'ppk_score',
        'ppk_comment',

        // ✅ Kolum lama (legacy - kalau masih ada code lama guna)
        'score',
        'comment',
    ];

    protected $casts = [
        'ppp_score' => 'integer',
        'ppk_score' => 'integer',
        'score'     => 'integer',
    ];

    public function evaluation()
    {
        return $this->belongsTo(\App\Models\PerformanceEvaluation::class, 'evaluation_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\PerformanceCompetencyItem::class, 'competency_item_id');
    }
}
