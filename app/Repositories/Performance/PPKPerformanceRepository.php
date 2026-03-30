<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceCompetencyItem;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;
use App\Models\PerformanceStatusLog;
use Illuminate\Support\Facades\DB;

class PPKPerformanceRepository
{
    public function getActivePeriod(): ?PerformancePeriod
    {
        return PerformancePeriod::where('is_active', 1)->first();
    }

    public function listForPPK(int $ppkUserId, int $periodId)
    {
        return PerformanceEvaluation::with(['assignment.pydUser','assignment.pppUser'])
            ->where('performance_period_id', $periodId)
            ->whereHas('assignment', function ($q) use ($ppkUserId) {
                $q->where('ppk_user_id', $ppkUserId);
            })
            ->orderByRaw("FIELD(status,'PPP_SCORED','PPK_APPROVED','SUBMITTED','DRAFT')")
            ->orderBy('updated_at','desc')
            ->get();
    }

    public function getEvaluationForPPK(int $evaluationId, int $ppkUserId): PerformanceEvaluation
    {
        return PerformanceEvaluation::with([
                'assignment.pydUser',
                'assignment.pppUser',
                'competencyScores.item'
            ])
            ->where('id', $evaluationId)
            ->whereHas('assignment', function ($q) use ($ppkUserId) {
                $q->where('ppk_user_id', $ppkUserId);
            })
            ->firstOrFail();
    }

    public function competencyItems()
    {
        return PerformanceCompetencyItem::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    public function approve(PerformanceEvaluation $eval, array $data, int $ppkUserId): void
    {
        if (!in_array($eval->status, ['PPP_SCORED'])) {
            return;
        }

        DB::transaction(function () use ($eval, $data, $ppkUserId) {

            $pppTotal = $eval->ppp_total_score ?? 0;

            $eval->update([
                'ppk_comment'     => $data['ppk_comment'] ?? null,
                'ppk_total_score' => $data['ppk_total_score'] ?? $pppTotal, // default ambil total PPP
                'status'          => 'PPK_APPROVED',
                'ppk_approved_at' => now(),
            ]);

            PerformanceStatusLog::create([
                'evaluation_id' => $eval->id,
                'from_status'   => 'PPP_SCORED',
                'to_status'     => 'PPK_APPROVED',
                'changed_by'    => $ppkUserId,
                'note'          => 'PPK mengesahkan penilaian.',
            ]);
        });
    }
}
