<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceAssignment;
use App\Models\PerformancePeriod;
use Illuminate\Support\Facades\DB;

class PerformanceAssignmentRepository
{
    /**
     * ✅ DIKEMASKINI: pilih period ikut TYPE (SKT/LNPT)
     * - Kalau $periodId diberi: pastikan period itu wujud + type match
     * - Kalau tak diberi: ambil period aktif untuk type itu
     * - Kalau tiada aktif: fallback latest untuk type itu (year desc, session desc)
     */
    public function getActiveOrSelectedPeriod(?int $periodId = null, string $type = 'LNPT'): ?PerformancePeriod
{
    $type = strtoupper(trim($type));

    if ($periodId) {
        return PerformancePeriod::where('id', $periodId)
            ->whereRaw('UPPER(TRIM(type)) = ?', [$type])
            ->first();
    }

    $active = PerformancePeriod::whereRaw('UPPER(TRIM(type)) = ?', [$type])
        ->where('is_active', 1)
        ->orderByDesc('year')
        ->orderByDesc('session')
        ->first();

    if ($active) {
        return $active;
    }

    return PerformancePeriod::whereRaw('UPPER(TRIM(type)) = ?', [$type])
        ->orderByDesc('year')
        ->orderByDesc('session')
        ->first();
}

    /**
     * ✅ DIKEMASKINI: list lantikan ikut period + TYPE
     * (Elak LNPT keluar dalam tab SKT dan sebaliknya)
     */
    public function listByPeriod(int $periodId, string $type = 'LNPT')
    {
        $type = strtoupper(trim($type));

        // ensure period betul-betul type yang diminta
        $period = PerformancePeriod::where('id', $periodId)
            // ✅ robust: elak isu 'SKT ' / 'skt'
            ->whereRaw('UPPER(TRIM(type)) = ?', [$type])
            ->first();

        if (!$period) {
            // kalau period id tak match type, pulangkan empty supaya UI tak bercampur
            return collect();
        }

        return PerformanceAssignment::with(['pydUser','pppUser','ppkUser'])
            ->where('performance_period_id', $periodId)
            ->orderBy('pyd_user_id')
            ->get();
    }

    public function store(array $data): PerformanceAssignment
    {
        return DB::transaction(function () use ($data) {

            return PerformanceAssignment::create([
                'performance_period_id' => (int)$data['performance_period_id'],
                'pyd_user_id'           => (int)$data['pyd_user_id'],
                'ppp_user_id'           => $data['ppp_user_id'] ?: null,
                'ppk_user_id'           => $data['ppk_user_id'] ?: null,
                'unit_id'               => $data['unit_id'] ?? null,
                'branch_id'             => $data['branch_id'] ?? null,
            ]);
        });
    }

    public function update(int $id, array $data): PerformanceAssignment
    {
        return DB::transaction(function () use ($id, $data) {

            $row = PerformanceAssignment::findOrFail($id);

            $row->update([
                'ppp_user_id' => $data['ppp_user_id'] ?: null,
                'ppk_user_id' => $data['ppk_user_id'] ?: null,
            ]);

            return $row;
        });
    }

    public function destroy(int $id): void
    {
        PerformanceAssignment::where('id', $id)->delete();
    }
}