<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PerformanceStatusLog;

class PerformanceEvaluation extends Model
{
    protected $fillable = [
        // =====================
        // RELATION / ASAS
        // =====================
        'performance_period_id',
        'assignment_id',
        'pyd_user_id',

        // =====================
        // STATUS & TARIKH FLOW
        // =====================
        'status',
        'submitted_at',
        'ppp_reviewed_at',
        'ppk_approved_at',

        // =====================
        // PYD INPUT
        // =====================
        'pyd_summary',
        'pyd_remarks',

        // Bahagian II (JSON)
        'bahagian_ii_data',

        // =====================
        // ✅ SKT (Sasaran Kerja Tahunan) - TAMBAH
        // =====================
        'skt_bahagian_i',
        'skt_bahagian_ii',
        'skt_bahagian_iii',
        'skt_submitted_at',
        'skt_ppp_reviewed_at',

        // =====================
        // PPP INPUT
        // =====================
        'ppp_total_score',
        'ppp_comment',

        // 🔽 BAHAGIAN VIII (PPP) – TAMBAH
        'ppp_supervise_years',
        'ppp_supervise_months',
        'ppp_overall_performance',
        'ppp_career_progress',

        // =====================
        // PPK INPUT
        // =====================
        'ppk_total_score',
        'ppk_comment',
        'ppk_supervise_years',
        'ppk_supervise_months',

        // =====================
        // ✅ PPSM (MANUAL ADMIN/UPSM) – TAMBAH
        // =====================
        'ppsm_score',
    ];

    protected $casts = [
        'submitted_at'     => 'datetime',
        'ppp_reviewed_at'  => 'datetime',
        'ppk_approved_at'  => 'datetime',
        'bahagian_ii_data' => 'array',

        // =====================
        // ✅ SKT (JSON + datetime) - TAMBAH
        // =====================
        'skt_bahagian_i'    => 'array',
        'skt_bahagian_ii'   => 'array',
        'skt_bahagian_iii'  => 'array',
        'skt_submitted_at'  => 'datetime',
        'skt_ppp_reviewed_at' => 'datetime',

        // ✅ PPSM (optional tapi elok supaya format decimal konsisten)
        'ppsm_score' => 'decimal:2',
    ];

    // =====================
    // RELATIONSHIPS
    // =====================

    public function period()
    {
        return $this->belongsTo(
            PerformancePeriod::class,
            'performance_period_id'
        );
    }

    public function assignment()
    {
        return $this->belongsTo(
            PerformanceAssignment::class,
            'assignment_id'
        );
    }

    // ✅ TAMBAH: user PYD yang dinilai
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'pyd_user_id'
        );
    }

    public function competencyScores()
    {
        return $this->hasMany(
            PerformanceCompetencyScore::class,
            'evaluation_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(PerformanceStatusLog::class, 'evaluation_id')->latest();
    }
}