<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceAssignment;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;
use App\Models\PerformanceStatusLog;
use Illuminate\Support\Facades\DB;

class PerformanceEvaluationAdminRepository
{
    public function periods()
    {
        return PerformancePeriod::orderByDesc('year')->get();
    }

    public function getPeriod(?int $periodId = null): ?PerformancePeriod
    {
        if ($periodId) return PerformancePeriod::find($periodId);

        return PerformancePeriod::where('is_active', 1)->first()
            ?: PerformancePeriod::orderByDesc('year')->first();
    }

    public function list(int $periodId, ?string $status = null)
    {
        $q = PerformanceEvaluation::with(['assignment.pydUser','assignment.pppUser','assignment.ppkUser'])
            ->where('performance_period_id', $periodId)
            ->orderByRaw("FIELD(status,'DRAFT','SUBMITTED','PPP_SCORED','PPK_APPROVED')") // susun ikut flow
            ->orderBy('updated_at','desc');

        if ($status && $status !== 'ALL') {
            $q->where('status', $status);
        }

        return $q->get();
    }

    public function findDetail(int $id): PerformanceEvaluation
    {
        return PerformanceEvaluation::with([
            'period',
            'assignment.pydUser',
            'assignment.pppUser',
            'assignment.ppkUser',
            'competencyScores.item',
            'logs' => fn($q) => $q->orderBy('changed_at')
        ])->findOrFail($id);
    }

    /**
     * Bulk create DRAFT evaluation untuk semua assignment dalam period yang belum ada evaluation.
     * Return: ['created' => x, 'skipped' => y]
     */
    public function bulkGenerate(int $periodId, int $adminUserId): array
    {
        return DB::transaction(function () use ($periodId, $adminUserId) {

            $assignments = PerformanceAssignment::where('performance_period_id', $periodId)->get();

            $created = 0;
            $skipped = 0;

            foreach ($assignments as $a) {
                $exists = PerformanceEvaluation::where('performance_period_id', $periodId)
                    ->where('pyd_user_id', $a->pyd_user_id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $eval = PerformanceEvaluation::create([
                    'performance_period_id' => $periodId,
                    'assignment_id'         => $a->id,
                    'pyd_user_id'           => $a->pyd_user_id,
                    'status'                => 'DRAFT',
                ]);

                PerformanceStatusLog::create([
                    'evaluation_id' => $eval->id,
                    'from_status'   => null,
                    'to_status'     => 'DRAFT',
                    'changed_by'    => $adminUserId,
                    'note'          => 'Bulk generate oleh Admin.',
                ]);

                $created++;
            }

            return compact('created','skipped');
        });
    }
}
