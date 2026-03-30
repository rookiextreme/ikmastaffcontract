<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;
use Illuminate\Http\Request;

class PerformanceDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = strtoupper((string) $request->get('tab', 'LNPT'));
        if (!in_array($tab, ['LNPT', 'SKT'])) {
            $tab = 'LNPT';
        }

        $periods = PerformancePeriod::query()
            ->where('type', $tab)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();

        $selectedPeriodId = $request->get('period_id');

        if ($selectedPeriodId) {
            $period = PerformancePeriod::query()
                ->where('type', $tab)
                ->find($selectedPeriodId);
        } else {
            $period = PerformancePeriod::query()
                ->where('type', $tab)
                ->where('is_active', 1)
                ->orderByDesc('year')
                ->orderByDesc('session')
                ->first();
        }

        $isSkt = $tab === 'SKT';

        $stats = [
            'total'         => 0,
            'draft'         => 0,
            'submitted'     => 0,
            'ppp_scored'    => 0, // LNPT
            'ppp_reviewed'  => 0, // SKT
            'ppk_approved'  => 0,
            'final'         => 0,
        ];

        $statusChartLabels = $isSkt
            ? ['DRAFT', 'SUBMITTED', 'PPP_REVIEWED']
            : ['DRAFT', 'SUBMITTED', 'PPP_SCORED', 'PPK_APPROVED', 'FINAL'];

        $statusChartValues = $isSkt
            ? [0, 0, 0]
            : [0, 0, 0, 0, 0];

        $recentEvaluations = collect();

        if ($period) {
            $baseQuery = PerformanceEvaluation::query()
                ->with(['user', 'period'])
                ->where('performance_period_id', $period->id);

            if ($isSkt) {
                $rawStats = (clone $baseQuery)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'DRAFT' THEN 1 ELSE 0 END) as draft,
                        SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
                        SUM(CASE WHEN status = 'PPP_REVIEWED' THEN 1 ELSE 0 END) as ppp_reviewed
                    ")
                    ->first();

                if ($rawStats) {
                    $stats = [
                        'total'         => (int) ($rawStats->total ?? 0),
                        'draft'         => (int) ($rawStats->draft ?? 0),
                        'submitted'     => (int) ($rawStats->submitted ?? 0),
                        'ppp_scored'    => 0,
                        'ppp_reviewed'  => (int) ($rawStats->ppp_reviewed ?? 0),
                        'ppk_approved'  => 0,
                        'final'         => 0,
                    ];
                }

                $statusChartValues = [
                    $stats['draft'],
                    $stats['submitted'],
                    $stats['ppp_reviewed'],
                ];
            } else {
                $rawStats = (clone $baseQuery)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'DRAFT' THEN 1 ELSE 0 END) as draft,
                        SUM(CASE WHEN status = 'SUBMITTED' THEN 1 ELSE 0 END) as submitted,
                        SUM(CASE WHEN status = 'PPP_SCORED' THEN 1 ELSE 0 END) as ppp_scored,
                        SUM(CASE WHEN status = 'PPK_APPROVED' THEN 1 ELSE 0 END) as ppk_approved,
                        SUM(CASE WHEN status = 'FINAL' THEN 1 ELSE 0 END) as final
                    ")
                    ->first();

                if ($rawStats) {
                    $stats = [
                        'total'         => (int) ($rawStats->total ?? 0),
                        'draft'         => (int) ($rawStats->draft ?? 0),
                        'submitted'     => (int) ($rawStats->submitted ?? 0),
                        'ppp_scored'    => (int) ($rawStats->ppp_scored ?? 0),
                        'ppp_reviewed'  => 0,
                        'ppk_approved'  => (int) ($rawStats->ppk_approved ?? 0),
                        'final'         => (int) ($rawStats->final ?? 0),
                    ];
                }

                $statusChartValues = [
                    $stats['draft'],
                    $stats['submitted'],
                    $stats['ppp_scored'],
                    $stats['ppk_approved'],
                    $stats['final'],
                ];
            }

            $recentEvaluations = (clone $baseQuery)
                ->latest('updated_at')
                ->limit(10)
                ->get();
        }

        return view('admin.performance.dashboard', compact(
            'tab',
            'isSkt',
            'periods',
            'period',
            'stats',
            'statusChartLabels',
            'statusChartValues',
            'recentEvaluations'
        ));
    }
}