<?php

namespace App\Http\Controllers\PPP\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceCompetencyScore;
use App\Models\PerformanceStatusLog; // ✅ tambah (log submit sahaja)
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PPPPerformanceController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $userId = Auth::id();

        $rows = PerformanceEvaluation::with(['assignment.pydUser','assignment.pppUser','assignment.ppkUser','period'])
            ->whereHas('assignment', function ($q) use ($userId) {
                $q->where('ppp_user_id', $userId);
            })
            // ✅ PPP hanya nampak bila PYD dah HANTAR atau selepas itu
            ->whereIn('status', ['SUBMITTED','PPP_SCORED','PPK_APPROVED'])
            ->orderByDesc('id')
            ->get();

        return view('ppp.performance.index', compact('rows'));
    }

    public function show(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with([
            'assignment.pydUser','assignment.pppUser','assignment.ppkUser',
            'period','competencyScores.item'
        ])->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppp_user_id ?? 0) !== (int)$userId) abort(403);

        // ✅ BLOCK DRAFT
        if ($evaluation->status === 'DRAFT') {
            return redirect()
                ->route('ppp.performance.index')
                ->with('error', 'PYD belum hantar borang. Status masih DRAFT.');
        }

        // ✅ PPP boleh buka semua bahagian (default III)
        $code = strtoupper(trim((string)$request->get('bahagian', 'III')));

        $allowedCodes = ['I','II','III','IV','V','VI','VII','VIII','IX'];
        if (!in_array($code, $allowedCodes, true)) $code = 'III';

        $bahagian = $code; // ✅ alias untuk blade yang guna $bahagian

        $sectionMeta = method_exists($this->repo, 'sectionMeta') ? $this->repo->sectionMeta() : [];

        $items = collect();
        if (in_array($code, ['III','IV','V','VI'], true)) {
            $items = method_exists($this->repo, 'getCompetencyItemsByCode')
                ? $this->repo->getCompetencyItemsByCode($code)
                : $this->repo->getCompetencyItems();
        }

        $scores = $evaluation->competencyScores()->get()->keyBy('competency_item_id');

        // ✅ wajib section
        $roleKey = 'ppp';
        $required = $this->repo->requiredSections($roleKey);

        // NOTE: map ini untuk nav sahaja (tak semestinya log)
        $sections = ['I','II','III','IV','V','VI','VII','VIII','IX'];
        $completedMap = [];
        foreach ($sections as $sec) {
            $completedMap[$sec] = $this->repo->isSectionComplete($sec, $roleKey, $evaluation);
        }

        // ✅ butang hantar akan depend pada ini
        $canSubmit = $this->repo->allRequiredComplete($roleKey, $evaluation);
        $missingSecs = $this->repo->incompleteRequiredSections($roleKey, $evaluation);

        return view('ppp.performance.show', compact(
            'evaluation','items','scores',
            'required','completedMap','canSubmit','missingSecs',
            'code','bahagian','sectionMeta'
        ));
    }

    public function save(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with(['assignment'])->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppp_user_id ?? 0) !== (int)$userId) abort(403);

        /**
         * ✅ FIX UTAMA:
         * PPP hanya boleh edit semasa status = SUBMITTED sahaja.
         * Bila dah hantar -> PPP_SCORED -> terus LOCK (tak boleh edit)
         */
        if ($evaluation->status !== 'SUBMITTED') {
            return back()->with('error', 'Borang telah dikunci. Status semasa: '.$evaluation->status);
        }

        $bahagian = strtoupper((string)$request->input('bahagian', $request->query('bahagian', '')));

        // =========================================================
        // ✅ BAHAGIAN VIII (PPP Ulasan) - tambah validation min 3
        // =========================================================
        if ($bahagian === 'VIII') {

            $data = $request->validate([
                'ppp_supervise_years'      => ['nullable', 'integer', 'min:0', 'max:99'],
                'ppp_supervise_months'     => ['nullable', 'integer', 'min:0', 'max:11'],

                // ✅ mesej error bila tak cukup aksara
                'ppp_overall_performance'  => ['required', 'string', 'min:3', 'max:5000'],
                'ppp_career_progress'      => ['required', 'string', 'min:3', 'max:5000'],
            ], [
                'ppp_overall_performance.required' => 'Sila isi (i) Prestasi keseluruhan.',
                'ppp_overall_performance.min'      => 'Prestasi keseluruhan mesti sekurang-kurangnya 3 aksara.',
                'ppp_career_progress.required'     => 'Sila isi (ii) Kemajuan kerjaya.',
                'ppp_career_progress.min'          => 'Kemajuan kerjaya mesti sekurang-kurangnya 3 aksara.',
            ]);

            $evaluation->update([
                'ppp_supervise_years'     => $data['ppp_supervise_years'] ?? null,
                'ppp_supervise_months'    => $data['ppp_supervise_months'] ?? null,
                'ppp_overall_performance' => $data['ppp_overall_performance'],
                'ppp_career_progress'     => $data['ppp_career_progress'],
            ]);

            // ✅ TIADA LOG SAVE_DRAFT
            return back()->with('success', 'Bahagian VIII berjaya disimpan.');
        }

        // =========================================================
        // ✅ BAHAGIAN III/IV/V/VI (Kompetensi) + ppp_comment
        // =========================================================
        $data = $request->validate([
            'ppp_scores'               => ['nullable', 'array'],
            'ppp_scores.*.score'       => ['nullable', 'integer', 'min:0', 'max:10'],
            'ppp_scores.*.comment'     => ['nullable', 'string', 'max:2000'],
            'ppp_comment'              => ['nullable', 'string', 'max:5000'],

            // legacy
            'scores'                   => ['nullable', 'array'],
            'scores.*.score'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'scores.*.comment'         => ['nullable', 'string', 'max:2000'],
        ]);

        $scoresArr = $data['ppp_scores'] ?? ($data['scores'] ?? []);

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
                    'ppp_score'   => $scoreVal,
                    'ppp_comment' => $comment,
                ]
            );
        }

        // ✅ total mentah (raw)
        $totalRaw = PerformanceCompetencyScore::where('evaluation_id', $evaluation->id)
            ->whereNotNull('ppp_score')
            ->sum('ppp_score');

        // ✅ total berwajaran (max 100)
        $weighted = method_exists($this->repo, 'computeWeightedFinalScore')
            ? $this->repo->computeWeightedFinalScore($evaluation->fresh(), 'ppp')
            : (float)$totalRaw;

        $evaluation->update([
            // kalau awak ada kolum raw, boleh simpan juga:
            // 'ppp_total_score_raw' => $totalRaw,

            // ✅ simpan yang berwajaran ke field yang sedia ada
            'ppp_total_score' => $weighted,
            'ppp_comment'     => $data['ppp_comment'] ?? $evaluation->ppp_comment,
        ]);

        // ✅ TIADA LOG SAVE_DRAFT
        return back()->with('success', 'Maklumat PPP berjaya disimpan.');
    }

    public function submit(Request $request, $evaluationId)
    {
        $userId = Auth::id();

        $evaluation = PerformanceEvaluation::with(['assignment','competencyScores'])
            ->findOrFail($evaluationId);

        if ((int)($evaluation->assignment->ppp_user_id ?? 0) !== (int)$userId) abort(403);

        // ✅ hanya boleh hantar jika SUBMITTED
        if ($evaluation->status !== 'SUBMITTED') {
            return back()->with('error', 'Status tidak membenarkan hantar ke PPK.');
        }

        // ✅ simpan status asal untuk log
        $fromStatus = $evaluation->status; // SUBMITTED

        // ✅ semak semua bahagian wajib PPP siap
        $missing = $this->repo->incompleteRequiredSections('ppp', $evaluation->fresh());
        if (!empty($missing)) {
            return back()->with('error', 'Sila lengkapkan bahagian wajib dahulu: '.implode(', ', $missing));
        }

        // ✅ safety: sekurang-kurangnya 1 markah PPP
        $count = PerformanceCompetencyScore::where('evaluation_id', $evaluation->id)
            ->whereNotNull('ppp_score')
            ->count();

        if ($count < 1) {
            return back()->with('error', 'Sila isi sekurang-kurangnya satu markah sebelum hantar ke PPK.');
        }

        // ✅ kira total mentah (raw)
        $totalRaw = PerformanceCompetencyScore::where('evaluation_id', $evaluation->id)
            ->whereNotNull('ppp_score')
            ->sum('ppp_score');

        // ✅ kira total berwajaran (max 100)
        $weighted = method_exists($this->repo, 'computeWeightedFinalScore')
            ? $this->repo->computeWeightedFinalScore($evaluation->fresh(), 'ppp')
            : (float)$totalRaw;

        // ✅ update status -> PPP_SCORED (selepas ini PPP locked)
        $evaluation->update([
            // kalau awak ada kolum raw, boleh simpan juga:
            // 'ppp_total_score_raw' => $totalRaw,

            // ✅ simpan berwajaran
            'ppp_total_score' => $weighted,

            'ppp_reviewed_at' => now(),
            'status'          => 'PPP_SCORED',
        ]);

        // ✅ LOG MASA HANTAR SAHAJA (PPP -> PPK) + DETAIL BAHAGIAN (PPP sahaja)
        $fresh = $evaluation->fresh();

        $requiredPPP = $this->repo->requiredSections('ppp'); // ['III','IV','V','VI','VIII']

        $completed = [];
        foreach ($requiredPPP as $sec) {
            if ($this->repo->isSectionComplete($sec, 'ppp', $fresh)) {
                $completed[] = $sec;
            }
        }

        $missing2 = $this->repo->incompleteRequiredSections('ppp', $fresh);

        PerformanceStatusLog::create([
            'evaluation_id' => $evaluation->id,
            'actor_id'      => $userId,
            'actor_role'    => 'ppp',
            'action'        => 'SUBMIT_PPP',
            'from_status'   => $fromStatus,       // ✅ SUBMITTED
            'to_status'     => 'PPP_SCORED',
            'meta'          => [
                'required_sections'  => $requiredPPP,
                'completed_sections' => $completed,
                'missing_sections'   => $missing2,

                // ✅ log dua-dua: raw & weighted (mudah audit)
                'ppp_total_score_raw' => (float)$totalRaw,
                'ppp_total_score'     => (float)$fresh->ppp_total_score,
            ],
        ]);

        return redirect()->route('ppp.performance.index')->with('success', 'Berjaya dihantar kepada PPK.');
    }
}
