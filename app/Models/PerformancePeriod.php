<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformancePeriod extends Model
{
    protected $fillable = [
        'year',
        'type',        // ✅ tambah
        'session',     // ✅ tambah
        'start_date',
        'end_date',
        'is_active',
        'note'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
        'session'    => 'integer',
    ];

    public function assignments()
    {
        return $this->hasMany(PerformanceAssignment::class, 'performance_period_id');
    }

    public function evaluations()
    {
        return $this->hasMany(PerformanceEvaluation::class, 'performance_period_id');
    }
}