<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceAssignment;
use App\Models\PerformanceCompetencyItem;
use App\Models\PerformanceCompetencyScore;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;
use App\Models\PerformanceStatusLog;
use Illuminate\Support\Facades\DB;

class PPPPerformanceRepository
{
    public function getActivePeriod(): ?PerformancePeriod
    {
        return PerformancePeriod::where('is_active', 1)->first();
    }

    /**
     * Senarai evaluations untuk PPP (ikut assignment)
     */
    public function listForPPP(int $pppUserId, int $periodId)
    {
        return PerformanceEvaluation::with(['assignment.pydUser','assignment.ppkUser'])
            ->where('performance_period_id', $periodId)
            ->whereHas('assignment', function ($q) use ($pppUserId) {
                $q->where('ppp_user_id', $pppUserId);
            })
            ->orderBy('status')
            ->orderBy('updated_at','desc')
            ->get();
    }

    /**
     * Security: pastikan evaluation ini milik PPP tersebut
     */
    public function getEvaluationForPPP(int $evaluationId, int $pppUserId): PerformanceEvaluation
    {
        return PerformanceEvaluation::with(['assignment.pydUser','assignment.ppkUser'])
            ->where('id', $evaluationId)
            ->whereHas('assignment', function ($q) use ($pppUserId) {
                $q->where('ppp_user_id', $pppUserId);
            })
            ->firstOrFail();
    }

    public function competencyItems()
    {
        return PerformanceCompetencyItem::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Save score & komen (tanpa tukar status)
     */
    public function saveScores(PerformanceEvaluation $eval, array $payload, int $pppUserId): void
    {
        // PPP hanya boleh edit bila PYD dah hantar atau PPP sedang buat semakan
        if (!in_array($eval->status, ['SUBMITTED','PPP_SCORED'])) {
            return;
        }

        DB::transaction(function () use ($eval, $payload) {

            $scores = $payload['scores'] ?? [];

            foreach ($scores as $itemId => $row) {
                PerformanceCompetencyScore::updateOrCreate(
                    [
                        'evaluation_id' => $eval->id,
                        'competency_item_id' => (int)$itemId,
                    ],
                    [
                        'score' => $row['score'] !== '' ? (float)$row['score'] : null,
                        'comment' => $row['comment'] ?? null,
                    ]
                );
            }

            // optional: kira total mudah (jumlah semua score)
            $total = PerformanceCompetencyScore::where('evaluation_id', $eval->id)->sum('score');

            $eval->update([
                'ppp_total_score' => $total,
                'ppp_comment'     => $payload['ppp_comment'] ?? null,
            ]);
        });
    }

    /**
     * Submit PPP -> set status PPP_SCORED
     */
    public function submitToPPK(PerformanceEvaluation $eval, int $pppUserId): void
    {
        if (!in_array($eval->status, ['SUBMITTED','PPP_SCORED'])) {
            return;
        }

        DB::transaction(function () use ($eval, $pppUserId) {

            $from = $eval->status;

            $eval->update([
                'status'          => 'PPP_SCORED',
                'ppp_reviewed_at' => now(),
            ]);

            PerformanceStatusLog::create([
                'evaluation_id' => $eval->id,
                'from_status'   => $from,
                'to_status'     => 'PPP_SCORED',
                'changed_by'    => $pppUserId,
                'note'          => 'PPP menghantar penilaian kepada PPK.',
            ]);
        });
    }
}
