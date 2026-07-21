<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;
use App\Models\PerformanceAssignment;
use App\Models\PerformanceStatusLog;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class PerformanceEvaluationAdminController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $tab  = strtolower((string)$request->get('tab', 'lnpt'));
        $type = $tab === 'skt' ? 'SKT' : 'LNPT';

        // ✅ tapis dropdown period ikut type
        $periods = PerformancePeriod::where('type', $type)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();

        $periodId = $request->get('period_id');
        $period = $periodId
            ? PerformancePeriod::where('type', $type)->find($periodId)
            : PerformancePeriod::where('type', $type)->where('is_active', 1)->first();

        if (!$period) {
            return view('admin.performance.evaluations.index', [
                'tab'     => $tab,
                'type'    => $type,
                'periods' => $periods,
                'period'  => null,
                'status'  => 'ALL',
                'rows'    => collect(),
                'ic'      => trim((string)$request->get('ic', '')),
                'message' => "Tiada Tempoh {$type} yang wujud / aktif.",
            ]);
        }

        $status = $request->get('status', 'ALL');
        $ic = trim((string)$request->get('ic', ''));

        $assignments = PerformanceAssignment::with(['pydUser', 'pppUser', 'ppkUser'])
            ->where('performance_period_id', $period->id)
            ->orderByDesc('id')
            ->get();

        if ($ic !== '') {
            $needle = str_replace(['-', ' '], '', $ic);

            $assignments = $assignments->filter(function ($a) use ($needle) {
                $userIc = (string)($a->pydUser->ic_no ?? '');
                $userIc = str_replace(['-', ' '], '', $userIc);

                return $userIc !== '' && str_contains($userIc, $needle);
            })->values();
        }

        $evalMap = PerformanceEvaluation::with([
                'assignment.pydUser',
                'assignment.pppUser',
                'assignment.ppkUser',
                'period'
            ])
            ->where('performance_period_id', $period->id)
            ->get()
            ->keyBy('assignment_id');

        // ✅ untuk SKT, status dipaparkan ikut flow SKT
        $rows = $assignments->map(function ($a) use ($evalMap, $type) {
            $e = $evalMap->get($a->id);

            $computedSktStatus = null;
            if ($type === 'SKT') {
                if (!$e) {
                    $computedSktStatus = 'BELUM_JANA';
                } elseif (($e?->status ?? null) === 'FINAL') {
                    $computedSktStatus = 'FINAL';
                } elseif (($e?->status ?? null) === 'PPP_REVIEWED') {
                    $computedSktStatus = 'PPP_REVIEWED';
                } elseif (!empty($e->skt_ppp_reviewed_at)) {
                    $computedSktStatus = 'PPP_REVIEWED';
                } elseif (!empty($e->skt_submitted_at)) {
                    $computedSktStatus = 'SUBMITTED';
                } else {
                    $computedSktStatus = 'DRAFT';
                }
            }

            return (object) [
                'assignment'      => $a,
                'evaluation'      => $e,
                'id'              => $e?->id,

                // ✅ LNPT guna status asal, SKT guna computed
                'status'          => $type === 'SKT'
                    ? $computedSktStatus
                    : ($e?->status ?? 'BELUM_JANA'),

                // ✅ tarikh ikut type
                'submitted_at'    => $type === 'SKT'
                    ? ($e?->skt_submitted_at)
                    : ($e?->submitted_at),

                'ppk_approved_at' => $type === 'SKT'
                    ? ($e?->skt_ppp_reviewed_at)
                    : ($e?->ppk_approved_at),

                // LNPT totals (SKT tiada)
                'ppk_total_score' => $e?->ppk_total_score,
                'ppp_total_score' => $e?->ppp_total_score,

                // LNPT PPSM (SKT tak guna)
                'ppsm_score'      => $e?->ppsm_score,
            ];
        });

        if ($status !== 'ALL') {
            $rows = $rows->filter(fn($r) => $r->status === $status)->values();
        }

        return view('admin.performance.evaluations.index', compact(
            'tab',
            'type',
            'periods',
            'period',
            'status',
            'rows',
            'ic'
        ));
    }

    public function show(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::with([
            'assignment.pydUser.staffPosition.position',
            'assignment.pydUser.staffPosition.grade',
            'assignment.pydUser',
            'assignment.pppUser',
            'assignment.ppkUser',
            'period',
            'competencyScores.item',
            'logs.actor',
        ])->findOrFail($id);

        /**
         * ==========================================================
         * ✅ FIX UTAMA: SKT JANGAN RENDER DI SINI
         * - Bila tab=skt atau period type SKT, redirect ke route SKT sebenar
         * ==========================================================
         */
        $reqTab = strtolower((string)$request->get('tab', ''));
        $isSkt  = (strtoupper((string)($evaluation->period?->type ?? '')) === 'SKT');

        if ($reqTab === 'skt' || $isSkt) {
            $bahagian = strtoupper((string)$request->get('bahagian', 'I'));

            return redirect()->route('admin.performance.skt.show', [
                'evaluation' => $evaluation->id,
                'bahagian'   => $bahagian,
            ]);
        }

        // ✅ LNPT sahaja
        $tab = 'lnpt';

        $bahagian = strtoupper((string)$request->get('bahagian', 'I'));
        $sectionMeta = $this->repo->sectionMeta();

        $pydGroup = strtoupper(
    trim((string) ($evaluation->assignment->pyd_group ?? 'BC'))
);

$items = $this->repo->getCompetencyItems();

if (in_array($bahagian, ['III', 'IV', 'V', 'VI'], true)) {
    $items = $this->repo->getCompetencyItemsByCode(
        $bahagian,
        $bahagian === 'V' ? $pydGroup : null
    );
}

        $scores = $evaluation->competencyScores()->get()->keyBy('competency_item_id');

        $roleKey = 'admin';
        $required = [];
        $sections = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX'];
        $completedMap = [];

        foreach ($sections as $sec) {
            $completedMap[$sec] = $this->repo->isSectionComplete($sec, $roleKey, $evaluation);
        }

        $baseUrl = route('admin.performance.evaluations.show', $evaluation->id) . '?tab=' . $tab;

        return view('admin.performance.evaluations.show', compact(
            'evaluation',
            'items',
            'scores',
            'required',
            'completedMap',
            'bahagian',
            'sectionMeta',
            'baseUrl',
            'tab'
        ));
    }

    public function bulkGenerate(Request $request)
    {
        $data = $request->validate([
            'period_id' => ['required', 'integer', 'exists:performance_periods,id'],
            'tab'       => ['nullable', 'in:lnpt,skt'],
        ]);

        $tab = strtolower((string)($data['tab'] ?? 'lnpt'));

        $period = PerformancePeriod::findOrFail($data['period_id']);

        // ✅ safety: paksa ikut type sebenar period
        if ($tab === 'skt' && strtoupper((string)$period->type) !== 'SKT') {
            $tab = 'lnpt';
        }
        if ($tab === 'lnpt' && strtoupper((string)$period->type) === 'SKT') {
            $tab = 'skt';
        }

        $assignments = PerformanceAssignment::where('performance_period_id', $period->id)->get();

        $created = 0;
        $updated = 0;

        foreach ($assignments as $a) {
            $eval = PerformanceEvaluation::firstOrCreate(
                [
                    'performance_period_id' => $period->id,
                    'assignment_id'         => $a->id,
                    'pyd_user_id'           => $a->pyd_user_id,
                ],
                [
                    'status' => 'DRAFT',
                ]
            );

            if ($eval->wasRecentlyCreated) {
    $created++;

    // ✅ Notification kepada PYD
if ($tab === 'skt') {

    NotificationHelper::send(
        $a->pyd_user_id,
        'SKT Baharu',
        'Sasaran Kerja Tahunan (SKT) baharu telah dijana. Sila lengkapkan SKT anda.',
        route('staff.performance.skt'),
        'SKT',
        'info'
    );

} else {

    NotificationHelper::send(
        $a->pyd_user_id,
        'Penilaian LNPT Baharu',
        'Penilaian LNPT baharu telah dijana. Sila lengkapkan borang penilaian anda.',
        route('staff.performance.index'),
        'LNPT',
        'info'
    );

}
}

            // ✅ kalau SKT, tandakan dijana
            if ($tab === 'skt' && empty($eval->skt_generated_at)) {
                $eval->forceFill([
                    'skt_generated_at' => now(),
                ])->save();

                $updated++;
            }
        }

        $message = $created > 0
            ? "Penjanaan penilaian berjaya. {$created} penilaian baharu telah dijana."
            : "Tiada penilaian baharu dijana. Semua lantikan telah mempunyai penilaian.";

        if ($tab === 'skt') {
            $message .= " (SKT: {$updated} rekod ditanda sebagai dijana.)";
        }

        return redirect()
            ->route('admin.performance.evaluations.index', [
                'period_id' => $period->id,
                'tab'       => $tab
            ])
            ->with('success', $message);
    }

    // ✅ Simpan MARKAH PPSM (manual) + LOG AUDIT UPDATE_PPSM
    public function updatePpsm(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::findOrFail($id);

        $data = $request->validate([
            'ppsm_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'reason'     => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $actorId = Auth::id();
        $old = $evaluation->ppsm_score;
        $new = $data['ppsm_score'] ?? null;

        $evaluation->update([
            'ppsm_score' => $new,
        ]);

        PerformanceStatusLog::create([
            'evaluation_id' => $evaluation->id,
            'actor_id'      => $actorId,
            'actor_role'    => 'admin',
            'action'        => 'UPDATE_PPSM',
            'from_status'   => $evaluation->status,
            'to_status'     => $evaluation->status,
            'meta'          => [
                'ppsm_from'  => $old,
                'ppsm_to'    => $new,
                'reason'     => $data['reason'],
                'ip'         => $request->ip(),
                'user_agent' => (string)$request->userAgent(),
            ],
        ]);

        return redirect()
            ->route('admin.performance.evaluations.show', [
                'id'       => $evaluation->id,
                'bahagian' => 'VII'
            ])
            ->with('success', 'Markah PPSM berjaya dikemaskini bersama sebab.');
    }

    // ✅ RESET PPP & PPK
    public function resetPppPpk(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::with(['competencyScores'])->findOrFail($id);

        if (!in_array($evaluation->status, ['SUBMITTED', 'PPP_SCORED', 'PPK_APPROVED'], true)) {
            return redirect()
                ->back()
                ->with('error', 'Reset hanya dibenarkan apabila status SUBMITTED / PPP_SCORED / PPK_APPROVED.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $actorId = Auth::id();

        $before = [
            'status'          => $evaluation->status,
            'ppp_total_score' => $evaluation->ppp_total_score,
            'ppk_total_score' => $evaluation->ppk_total_score,
            'ppp_reviewed_at' => $evaluation->ppp_reviewed_at,
            'ppk_approved_at' => $evaluation->ppk_approved_at,
            'ppsm_score'      => $evaluation->ppsm_score,
        ];

        DB::transaction(function () use ($evaluation, $data, $actorId, $request, $before) {
            $evaluation->competencyScores()->update([
                'ppp_score'   => null,
                'ppp_comment' => null,
                'ppk_score'   => null,
                'ppk_comment' => null,
            ]);

            $evaluation->forceFill([
                'ppp_total_score'         => null,
                'ppk_total_score'         => null,
                'ppp_reviewed_at'         => null,
                'ppk_approved_at'         => null,
                'ppp_comment'             => null,
                'ppp_supervise_years'     => null,
                'ppp_supervise_months'    => null,
                'ppp_overall_performance' => null,
                'ppp_career_progress'     => null,
                'ppk_comment'             => null,
                'ppk_supervise_years'     => null,
                'ppk_supervise_months'    => null,
                'status'                  => 'SUBMITTED',
            ])->save();

            PerformanceStatusLog::create([
                'evaluation_id' => $evaluation->id,
                'actor_id'      => $actorId,
                'actor_role'    => 'admin',
                'action'        => 'ADMIN_RESET_PPP_PPK',
                'from_status'   => $before['status'],
                'to_status'     => 'SUBMITTED',
                'meta'          => [
                    'reason'     => $data['reason'],
                    'before'     => $before,
                    'ip'         => $request->ip(),
                    'user_agent' => (string)$request->userAgent(),
                ],
            ]);
        });

        return redirect()
            ->route('admin.performance.evaluations.show', [
                'id'       => $evaluation->id,
                'bahagian' => 'III'
            ])
            ->with('success', 'Reset PPP & PPK berjaya. Status kembali ke SUBMITTED (menunggu PPP isi semula).');
    }

    // ✅ RESET PPK SAHAJA
    public function resetPpk(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::with(['competencyScores'])->findOrFail($id);

        if (!in_array($evaluation->status, ['PPP_SCORED', 'PPK_APPROVED'], true)) {
            return redirect()
                ->back()
                ->with('error', 'Reset PPK hanya dibenarkan apabila status PPP_SCORED / PPK_APPROVED.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $actorId = Auth::id();

        $before = [
            'status'               => $evaluation->status,
            'ppk_total_score'      => $evaluation->ppk_total_score,
            'ppk_comment'          => $evaluation->ppk_comment,
            'ppk_supervise_years'  => $evaluation->ppk_supervise_years,
            'ppk_supervise_months' => $evaluation->ppk_supervise_months,
            'ppk_approved_at'      => $evaluation->ppk_approved_at,
            'ppsm_score'           => $evaluation->ppsm_score,
        ];

        DB::transaction(function () use ($evaluation, $data, $actorId, $before) {
            $evaluation->competencyScores()->update([
                'ppk_score'   => null,
                'ppk_comment' => null,
            ]);

            $evaluation->forceFill([
                'ppk_total_score'      => null,
                'ppk_comment'          => null,
                'ppk_supervise_years'  => null,
                'ppk_supervise_months' => null,
                'ppk_approved_at'      => null,
                'status'               => 'PPP_SCORED',
            ])->save();

            PerformanceStatusLog::create([
                'evaluation_id' => $evaluation->id,
                'actor_id'      => $actorId,
                'actor_role'    => 'admin',
                'action'        => 'ADMIN_RESET_PPK',
                'from_status'   => $before['status'],
                'to_status'     => 'PPP_SCORED',
                'meta'          => [
                    'reason' => $data['reason'],
                    'before' => $before,
                ],
            ]);
        });

        return redirect()
            ->route('admin.performance.evaluations.show', [
                'id'       => $evaluation->id,
                'bahagian' => 'IX'
            ])
            ->with('success', 'Reset PPK berjaya. Status kembali ke PPP_SCORED (PPK boleh isi semula).');
    }

    // ✅ FINALIZE OLEH ADMIN (LNPT + SKT)
    public function finalize(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::with(['period', 'assignment'])->findOrFail($id);

        $type = strtoupper((string)($evaluation->period?->type ?? ''));

        if (!in_array($type, ['LNPT', 'SKT'], true)) {
            return redirect()
                ->back()
                ->with('error', 'Jenis penilaian tidak sah untuk proses muktamad.');
        }

        if ($evaluation->status === 'FINAL') {
            return redirect()
                ->back()
                ->with('info', 'Penilaian ini telah dimuktamadkan sebelum ini.');
        }

        // ✅ LNPT: finalize selepas PPK_APPROVED
        if ($type === 'LNPT' && $evaluation->status !== 'PPK_APPROVED') {
            return redirect()
                ->back()
                ->with('error', 'LNPT hanya boleh dimuktamadkan apabila status semasa ialah PPK_APPROVED.');
        }

        // ✅ SKT: finalize selepas PPP_REVIEWED
        if ($type === 'SKT' && $evaluation->status !== 'PPP_REVIEWED') {
            return redirect()
                ->back()
                ->with('error', 'SKT hanya boleh dimuktamadkan apabila status semasa ialah PPP_REVIEWED.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $actorId = Auth::id();
        $fromStatus = $evaluation->status;

        DB::transaction(function () use ($evaluation, $request, $data, $actorId, $fromStatus) {
            $evaluation->forceFill([
                'status' => 'FINAL',
            ])->save();

            PerformanceStatusLog::create([
                'evaluation_id' => $evaluation->id,
                'actor_id'      => $actorId,
                'actor_role'    => 'admin',
                'action'        => 'ADMIN_FINALIZE',
                'from_status'   => $fromStatus,
                'to_status'     => 'FINAL',
                'meta'          => [
                    'reason'     => $data['reason'],
                    'ip'         => $request->ip(),
                    'user_agent' => (string)$request->userAgent(),
                    'type'       => $evaluation->period?->type,
                ],
            ]);
        });

        if ($type === 'SKT') {
            return redirect()
                ->route('admin.performance.skt.show', [
                    'evaluation' => $evaluation->id,
                    'bahagian'   => 'III',
                ])
                ->with('success', 'SKT berjaya dimuktamadkan oleh admin. Status kini ialah FINAL.');
        }

        return redirect()
            ->route('admin.performance.evaluations.show', [
                'id'       => $evaluation->id,
                'bahagian' => 'IX',
            ])
            ->with('success', 'Penilaian LNPT berjaya dimuktamadkan. Status kini ialah FINAL.');
    }

    /**
     * ✅ Admin kemaskini text PYD (Bahagian II)
     */
    public function updatePydText(Request $request, $id)
    {
        $evaluation = PerformanceEvaluation::findOrFail($id);

        $data = $request->validate([
            'bahagian'     => ['required', 'in:II'],
            'bahagian_ii'  => ['nullable', 'array'],

            'bahagian_ii.kegiatan' => ['nullable', 'array'],
            'bahagian_ii.kegiatan.*.aktiviti'  => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.kegiatan.*.peringkat' => ['nullable', 'string', 'max:5000'],

            'bahagian_ii.latihan_hadir' => ['nullable', 'array'],
            'bahagian_ii.latihan_hadir.*.nama'   => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.latihan_hadir.*.tarikh' => ['nullable', 'string', 'max:200'],
            'bahagian_ii.latihan_hadir.*.tempat' => ['nullable', 'string', 'max:5000'],

            'bahagian_ii.latihan_perlu' => ['nullable', 'array'],
            'bahagian_ii.latihan_perlu.*.bidang' => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.latihan_perlu.*.sebab'  => ['nullable', 'string', 'max:5000'],

            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $before = [
            'bahagian_ii_data' => $evaluation->bahagian_ii_data,
        ];

        $bahagianII = $data['bahagian_ii'] ?? null;

        if (is_array($bahagianII)) {
            $bahagianII['kegiatan'] = array_values(array_filter($bahagianII['kegiatan'] ?? [], function ($r) {
                $a = trim((string)($r['aktiviti'] ?? ''));
                $p = trim((string)($r['peringkat'] ?? ''));
                return $a !== '' || $p !== '';
            }));

            $bahagianII['latihan_hadir'] = array_values(array_filter($bahagianII['latihan_hadir'] ?? [], function ($r) {
                $n = trim((string)($r['nama'] ?? ''));
                $t = trim((string)($r['tarikh'] ?? ''));
                $m = trim((string)($r['tempat'] ?? ''));
                return $n !== '' || $t !== '' || $m !== '';
            }));

            $bahagianII['latihan_perlu'] = array_values(array_filter($bahagianII['latihan_perlu'] ?? [], function ($r) {
                $b = trim((string)($r['bidang'] ?? ''));
                $s = trim((string)($r['sebab'] ?? ''));
                return $b !== '' || $s !== '';
            }));

            $isAllEmpty =
                empty($bahagianII['kegiatan']) &&
                empty($bahagianII['latihan_hadir']) &&
                empty($bahagianII['latihan_perlu']);

            $bahagianII = $isAllEmpty ? null : $bahagianII;
        }

        $evaluation->update([
            'bahagian_ii_data' => $bahagianII,
        ]);

        $after = [
            'bahagian_ii_data' => $evaluation->fresh()->bahagian_ii_data,
        ];

        PerformanceStatusLog::create([
            'evaluation_id' => $evaluation->id,
            'actor_id'      => Auth::id(),
            'actor_role'    => 'admin',
            'action'        => 'ADMIN_UPDATE_PYD',
            'from_status'   => $evaluation->status,
            'to_status'     => $evaluation->status,
            'meta'          => [
                'reason' => $data['reason'],
                'before' => $before,
                'after'  => $after,
            ],
        ]);

        return redirect()
            ->route('admin.performance.evaluations.show', [
                'id'       => $evaluation->id,
                'bahagian' => 'II'
            ])
            ->with('success', 'Maklumat PYD (Bahagian II) berjaya dikemaskini.');
    }
    public function pdf($id)
{
    $evaluation = PerformanceEvaluation::with([
        'assignment.pydUser.staffPosition.position',
        'assignment.pydUser.staffPosition.grade',
        'assignment.pydUser',
        'assignment.pppUser',
        'assignment.ppkUser',
        'period',
        'competencyScores.item',
        'logs.actor',
    ])->findOrFail($id);

    if (strtoupper((string)$evaluation->status) !== 'FINAL') {
        return redirect()
            ->back()
            ->with('error', 'PDF hanya boleh dijana selepas penilaian dimuktamadkan.');
    }

    $sectionMeta = $this->repo->sectionMeta();

    $pydGroup = strtoupper(
    trim((string) ($evaluation->assignment->pyd_group ?? 'BC'))
);

$itemsBySection = [
    'III' => $this->repo->getCompetencyItemsByCode('III'),
    'IV'  => $this->repo->getCompetencyItemsByCode('IV'),
    'V'   => $this->repo->getCompetencyItemsByCode(
        'V',
        $pydGroup
    ),
    'VI'  => $this->repo->getCompetencyItemsByCode('VI'),
];

    $scores = $evaluation->competencyScores()->get()->keyBy('competency_item_id');

    $pdf = Pdf::loadView('admin.performance.evaluations.pdf', compact(
    'evaluation',
    'sectionMeta',
    'itemsBySection',
    'scores',
    'pydGroup'
))->setPaper('a4', 'portrait');

    $filename = 'laporan-penilaian-prestasi-' . $evaluation->id . '.pdf';

    return $pdf->stream($filename);
}
}