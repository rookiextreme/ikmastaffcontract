<?php

namespace App\Http\Controllers\PPP\Performance;

use App\Models\PerformanceStatusLog;
use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class PPPSktController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Senarai SKT untuk disemak
     */
    public function index()
    {
        $userId = Auth::id();
        $period = $this->repo->getActivePeriod('SKT');

        if (!$period) {
            return view('ppp.performance.skt.index', [
                'period' => null,
                'rows'   => collect(),
                'message'=> 'Tiada Tempoh SKT aktif.',
            ]);
        }

        $rows = PerformanceEvaluation::with(['assignment.pydUser','assignment.pppUser'])
            ->where('performance_period_id', $period->id)
            ->whereHas('assignment', fn($q) => $q->where('ppp_user_id', $userId))
            ->whereIn('status', ['SUBMITTED','PPP_REVIEWED'])
            ->get();

        return view('ppp.performance.skt.index', compact('period','rows'));
    }

    /**
     * Papar SKT (PPP)
     */
    public function show(PerformanceEvaluation $evaluation)
    {
        abort_if($evaluation->assignment?->ppp_user_id !== Auth::id(), 403);

        return view('performance.skt.show', [
            'period'     => $evaluation->period,
            'assignment' => $evaluation->assignment,
            'evaluation' => $evaluation,
            'bahagian'   => request('bahagian','I'),
            'baseUrl'    => route('ppp.performance.skt.show', $evaluation->id),
            'message'    => null,
            'roleKey'    => 'ppp',
        ]);
    }

    /**
     * Simpan ulasan PPP
     */
    public function save(Request $request, PerformanceEvaluation $evaluation)
    {
        abort_if($evaluation->assignment?->ppp_user_id !== Auth::id(), 403);

        if (!in_array($evaluation->status, ['SUBMITTED','PPP_REVIEWED'])) {
            return back()->with('error','Belum sampai fasa PPP.');
        }

        $evaluation->update([
            'skt_bahagian_iii' => [
                'ulasan_pyd' => $evaluation->skt_bahagian_iii['ulasan_pyd'] ?? null,
                'ulasan_ppp' => $request->input('skt_bahagian_iii.ulasan_ppp'),
            ],
        ]);

        return back()->with('success','Ulasan PPP disimpan.');
    }

    /**
     * Sahkan SKT
     */
    public function submit(Request $request, PerformanceEvaluation $evaluation)
    {
        abort_if($evaluation->assignment?->ppp_user_id !== Auth::id(), 403);

        $evaluation->update([
            'status' => 'PPP_REVIEWED',
            'skt_ppp_reviewed_at' => now(),
        ]);

        $fresh = $evaluation->fresh();

$requiredPPP = ['I', 'II', 'III'];
$completed = [];
foreach ($requiredPPP as $sec) {
    if ($this->repo->isSectionComplete($sec, 'ppp', $fresh)) {
        $completed[] = $sec;
    }
}

$missing = [];
foreach ($requiredPPP as $sec) {
    if (!$this->repo->isSectionComplete($sec, 'ppp', $fresh)) {
        $missing[] = $sec;
    }
}

PerformanceStatusLog::create([
    'evaluation_id' => $fresh->id,
    'actor_id'      => Auth::id(),
    'actor_role'    => 'ppp',
    'action'        => 'REVIEW_PPP_SKT',
    'from_status'   => 'SUBMITTED',
    'to_status'     => 'PPP_REVIEWED',
    'meta'          => [
        'module'             => 'SKT',
        'required_sections'  => $requiredPPP,
        'completed_sections' => $completed,
        'missing_sections'   => $missing,
    ],
]);

// ✅ Notification kepada PYD selepas PPP sahkan SKT
if ($fresh->pyd_user_id) {
    NotificationHelper::send(
        $fresh->pyd_user_id,
        'SKT Telah Disemak PPP',
        Auth::user()->name.' telah selesai menyemak SKT anda.',
        route('staff.performance.skt'),
        'SKT',
        'success'
    );
}

        return back()->with('success','SKT disahkan oleh PPP.');
    }
}