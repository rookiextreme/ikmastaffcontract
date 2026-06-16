<?php

namespace App\Http\Controllers\PPK\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceCompetencyScore;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class PPKPerformanceController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $userId = Auth::id();

        // ✅ LIST PPK: yang memang sampai kepada PPK
        // status mesti PPP_SCORED (untuk semakan) atau PPK_APPROVED (sejarah)
        $rows = PerformanceEvaluation::with(['assignment.pydUser','assignment.pppUser','assignment.ppkUser','period'])
            ->whereHas('assignment', function ($q) use ($userId) {
                $q->where('ppk_user_id', $userId);
            })
            ->whereIn('status', ['PPP_SCORED','PPK_APPROVED'])
            ->orderByDesc('id')
            ->get();

        return view('ppk.performance.index', compact('rows'));
    }

    public function show(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with([
            'assignment.pydUser','assignment.pppUser','assignment.ppkUser',
            'period','competencyScores.item'
        ])->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppk_user_id ?? 0) !== (int)$userId) abort(403);

        // ✅ PPK boleh buka semua bahagian
        $bahagian = strtoupper(trim((string)$request->get('bahagian', 'III')));
        $allowed  = ['I','II','III','IV','V','VI','VII','VIII','IX'];
        if (!in_array($bahagian, $allowed, true)) $bahagian = 'III';

        $sectionMeta = method_exists($this->repo, 'sectionMeta') ? $this->repo->sectionMeta() : [];

        // items hanya untuk kompetensi
        $items = collect();
        if (in_array($bahagian, ['III','IV','V','VI'], true)) {
            $items = method_exists($this->repo, 'getCompetencyItemsByCode')
                ? $this->repo->getCompetencyItemsByCode($bahagian)
                : $this->repo->getCompetencyItems();
        }

        $scores = $evaluation->competencyScores()->get()->keyBy('competency_item_id');

        // required map
        $roleKey = 'ppk';
        $required = $this->repo->requiredSections($roleKey);
        $sections = ['I','II','III','IV','V','VI','VII','VIII','IX'];
        $completedMap = [];
        foreach ($sections as $sec) {
            $completedMap[$sec] = $this->repo->isSectionComplete($sec, $roleKey, $evaluation);
        }
        $canApprove = $this->repo->allRequiredComplete($roleKey, $evaluation);
        $missingSecs = $this->repo->incompleteRequiredSections($roleKey, $evaluation);

        return view('ppk.performance.show', compact(
            'evaluation','items','scores',
            'required','completedMap','canApprove','missingSecs',
            'bahagian','sectionMeta'
        ));
    }

    public function save(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with(['assignment'])->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppk_user_id ?? 0) !== (int)$userId) abort(403);

        // ✅ PPK hanya boleh isi bila status sudah sampai PPK
        if ($evaluation->status !== 'PPP_SCORED') {
            return back()->with('error', 'Status tidak membenarkan PPK mengemaskini.');
        }

        $bahagian = strtoupper(trim((string)$request->input('bahagian', 'III')));

        // ✅ Bahagian IX: ulasan PPK + tempoh pengawasan (tahun/bulan)
        if ($bahagian === 'IX') {
            $data = $request->validate([
                'bahagian'              => ['required','in:IX'],

                // ✅ tambahan: tahun & bulan
                'ppk_supervise_years'   => ['nullable', 'integer', 'min:0', 'max:99'],
                'ppk_supervise_months'  => ['nullable', 'integer', 'min:0', 'max:11'],

                'ppk_comment'           => ['required','string','min:3','max:5000'],
            ]);

            $evaluation->update([
                'ppk_supervise_years'  => $data['ppk_supervise_years'] ?? null,
                'ppk_supervise_months' => $data['ppk_supervise_months'] ?? null,
                'ppk_comment'          => $data['ppk_comment'],
            ]);

            // ✅ TIADA LOG SAVE_DRAFT (ikut polisi baru)
            return redirect()
                ->route('ppk.performance.show', [$evaluation->id, 'bahagian' => 'IX'])
                ->with('success', 'Ulasan PPK berjaya disimpan.');
        }

        // ✅ Bahagian III–VI: markah/komen PPK
        if (in_array($bahagian, ['III','IV','V','VI'], true)) {

            $data = $request->validate([
                'bahagian'               => ['required','in:III,IV,V,VI'],
                'ppk_scores'             => ['nullable','array'],
                'ppk_scores.*.score'     => ['nullable','integer','min:0','max:10'],
                'ppk_scores.*.comment'   => ['nullable','string','max:2000'],
            ]);

            $scoresArr = $data['ppk_scores'] ?? [];

            foreach ($scoresArr as $itemId => $payload) {
                $scoreVal = $payload['score'] ?? null;
                $comment  = $payload['comment'] ?? null;

                if ($scoreVal === null && ($comment === null || $comment === '')) continue;

                PerformanceCompetencyScore::updateOrCreate(
                    [
                        'evaluation_id'      => $evaluation->id,
                        'competency_item_id' => (int)$itemId,
                    ],
                    [
                        'ppk_score'   => $scoreVal,
                        'ppk_comment' => $comment,
                    ]
                );
            }

            // ✅ total mentah (raw)
            $totalRaw = PerformanceCompetencyScore::where('evaluation_id', $evaluation->id)
                ->whereNotNull('ppk_score')
                ->sum('ppk_score');

            // ✅ total berwajaran (max 100)
            $weighted = method_exists($this->repo, 'computeWeightedFinalScore')
                ? $this->repo->computeWeightedFinalScore($evaluation->fresh(), 'ppk')
                : (float)$totalRaw;

            $evaluation->update([
                // kalau awak ada kolum raw, boleh simpan juga:
                // 'ppk_total_score_raw' => $totalRaw,

                // ✅ simpan yang berwajaran ke field sedia ada
                'ppk_total_score' => $weighted,
            ]);

            // ✅ TIADA LOG SAVE_DRAFT (ikut polisi baru)
            return redirect()
                ->route('ppk.performance.show', [$evaluation->id, 'bahagian' => $bahagian])
                ->with('success', 'Markah PPK berjaya disimpan.');
        }

        return back()->with('info', 'Bahagian ini paparan sahaja.');
    }

    public function approve(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with(['assignment'])->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppk_user_id ?? 0) !== (int)$userId) abort(403);

        if ($evaluation->status !== 'PPP_SCORED') {
            return back()->with('error', 'Status tidak membenarkan pengesahan PPK.');
        }

        $missing = $this->repo->incompleteRequiredSections('ppk', $evaluation->fresh());
        if (!empty($missing)) {
            return back()->with('error', 'Sila lengkapkan bahagian wajib dahulu: '.implode(', ', $missing));
        }

        // ✅ pastikan nilai berwajaran latest sebelum approve
        $totalRaw = PerformanceCompetencyScore::where('evaluation_id', $evaluation->id)
            ->whereNotNull('ppk_score')
            ->sum('ppk_score');

        $weighted = method_exists($this->repo, 'computeWeightedFinalScore')
            ? $this->repo->computeWeightedFinalScore($evaluation->fresh(), 'ppk')
            : (float)$totalRaw;

        $evaluation->update([
            // kalau awak ada kolum raw, boleh simpan juga:
            // 'ppk_total_score_raw' => $totalRaw,

            'ppk_total_score' => $weighted,
            'ppk_approved_at' => now(),
            'status'          => 'PPK_APPROVED',
        ]);

        // =====================================================
        // ✅ LOG APPROVE PPK (PPK SAHAJA, BUKAN SEMUA)
        // =====================================================
        $fresh = $evaluation->fresh();

        // hanya bahagian wajib PPK
        $requiredPPK = $this->repo->requiredSections('ppk');

        $completed = [];
        foreach ($requiredPPK as $sec) {
            if ($this->repo->isSectionComplete($sec, 'ppk', $fresh)) {
                $completed[] = $sec;
            }
        }

        $missing = $this->repo->incompleteRequiredSections('ppk', $fresh);

        \App\Models\PerformanceStatusLog::create([
            'evaluation_id' => $evaluation->id,
            'actor_id'      => $userId,
            'actor_role'    => 'ppk',
            'action'        => 'APPROVE_PPK',
            'from_status'   => 'PPP_SCORED',
            'to_status'     => 'PPK_APPROVED',
            'meta'          => [
                'required_sections'    => $requiredPPK,
                'completed_sections'   => $completed,
                'missing_sections'     => $missing,

                // ✅ simpan dua-dua untuk audit
                'ppk_total_score_raw'  => (float)$totalRaw,
                'final_score'          => (float)($fresh->ppk_total_score ?? 0),
            ],
        ]);

        // ✅ Notification kepada PYD selepas PPK sahkan LNPT
$fresh2 = PerformanceEvaluation::with('assignment')->find($evaluation->id);

if ($fresh2 && $fresh2->pyd_user_id) {
    NotificationHelper::send(
        $fresh2->pyd_user_id,
        'LNPT Telah Disahkan',
        'LNPT anda telah disahkan oleh PPK dan proses penilaian telah selesai.',
        route('staff.performance.index'),
        'LNPT',
        'success'
    );
}

        return redirect()->route('ppk.performance.index')->with('success', 'Berjaya disahkan oleh PPK.');
    }
}
