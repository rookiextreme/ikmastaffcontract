<?php

namespace App\Repositories\Performance;

use App\Models\PerformancePeriod;
use App\Models\PerformanceAssignment;
use Illuminate\Support\Facades\DB;


class PerformancePeriodRepository
{
    /**
     * List period ikut type:
     * - LNPT (default)
     * - SKT
     */
    public function list(string $type = 'LNPT')
    {
        $type = strtoupper(trim($type));

        return PerformancePeriod::query()
            ->where('type', $type)
            ->withCount('assignments')
            ->orderByDesc('is_active')
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();
    }

    /**
     * Store period (LNPT/SKT)
     * - jika is_active=1: auto-off period lain untuk type yang sama
     */
    public function store(array $data): PerformancePeriod
    {
        $type = strtoupper(trim($data['type'] ?? 'LNPT'));

        return DB::transaction(function () use ($data, $type) {

            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($isActive) {
                PerformancePeriod::where('type', $type)->update(['is_active' => 0]);
            }

            $payload = $data;
            $payload['type'] = $type;
            $payload['is_active'] = $isActive;

            return PerformancePeriod::create($payload);
        });
    }

    /**
     * Update period
     * - jika is_active=1: auto-off period lain type yang sama
     */
    public function update(int $id, array $data): PerformancePeriod
    {
        return DB::transaction(function () use ($id, $data) {

            $period = PerformancePeriod::findOrFail($id);

            $type = strtoupper(trim($period->type ?? ($data['type'] ?? 'LNPT')));

            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($isActive) {
                PerformancePeriod::where('type', $type)
                    ->where('id', '!=', $period->id)
                    ->update(['is_active' => 0]);
            }

            $payload = $data;
            // ✅ jangan benarkan tukar type melalui update biasa (selamat)
            unset($payload['type']);

            $payload['is_active'] = $isActive;

            $period->update($payload);

            return $period;
        });
    }

    /**
     * Activate period by id
     * - auto-off period lain utk type yang sama sahaja
     */
    public function activate(int $id): void
    {
        DB::transaction(function () use ($id) {

            $period = PerformancePeriod::findOrFail($id);
            $type = strtoupper(trim($period->type ?? 'LNPT'));

            PerformancePeriod::where('type', $type)->update(['is_active' => 0]);

            $period->update(['is_active' => 1]);
        });
    }

    /**
     * Optional helper: get active period ikut type
     */
    public function getActive(string $type = 'LNPT'): ?PerformancePeriod
    {
        $type = strtoupper(trim($type));

        return PerformancePeriod::query()
            ->where('type', $type)
            ->where('is_active', 1)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();
    }
    /**
 * Clone semua lantikan daripada tempoh sebelumnya
 * ke tempoh yang dipilih.
 */
public function clonePreviousAssignments(int $targetPeriodId): array
{
    return DB::transaction(function () use ($targetPeriodId) {

        // Tempoh sasaran, contoh 2027
        $targetPeriod = PerformancePeriod::findOrFail($targetPeriodId);

        $type = strtoupper(trim($targetPeriod->type ?? 'LNPT'));

        // Elak clone dua kali
        $targetHasAssignments = PerformanceAssignment::where(
            'performance_period_id',
            $targetPeriod->id
        )->exists();

        if ($targetHasAssignments) {
            return [
                'success' => false,
                'message' => 'Tempoh ini telah mempunyai lantikan. Proses salinan dibatalkan.',
            ];
        }

        // Cari tempoh sebelumnya bagi jenis yang sama
        $previousPeriod = PerformancePeriod::query()
            ->where('type', $type)
            ->where('year', '<', $targetPeriod->year)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();

        if (!$previousPeriod) {
            return [
                'success' => false,
                'message' => 'Tiada tempoh sebelumnya dijumpai untuk disalin.',
            ];
        }

        // Ambil semua lantikan tempoh sebelumnya
        $previousAssignments = PerformanceAssignment::where(
            'performance_period_id',
            $previousPeriod->id
        )->get();

        if ($previousAssignments->isEmpty()) {
            return [
                'success' => false,
                'message' => "Tiada lantikan pada tempoh {$previousPeriod->year}.",
            ];
        }

        $count = 0;

        foreach ($previousAssignments as $assignment) {

            PerformanceAssignment::create([
                'performance_period_id' => $targetPeriod->id,

                'pyd_user_id' => $assignment->pyd_user_id,
                'pyd_group'   => $assignment->pyd_group,

                'ppp_user_id' => $assignment->ppp_user_id,
                'ppk_user_id' => $assignment->ppk_user_id,

                'unit_id'     => $assignment->unit_id,
                'branch_id'   => $assignment->branch_id,
            ]);

            $count++;
        }

        return [
            'success' => true,
            'count'   => $count,
            'source_period' => $previousPeriod,
            'target_period' => $targetPeriod,
            'message' => "{$count} lantikan berjaya disalin daripada {$previousPeriod->year} ke {$targetPeriod->year}.",
        ];
    });
}
}