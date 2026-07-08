<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Models\PerformanceStatusLog;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminSktController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function show(Request $request, PerformanceEvaluation $evaluation)
    {
        // ❌ bukan SKT
        abort_if(strtoupper($evaluation->period?->type) !== 'SKT', 404);

        $bahagian = strtoupper((string)$request->get('bahagian','I'));

        return view('performance.skt.show', [
            'evaluation' => $evaluation,
            'period'     => $evaluation->period,
            'assignment' => $evaluation->assignment,
            'bahagian'   => $bahagian,
            'baseUrl'    => route('admin.performance.skt.show', $evaluation->id),

            // 🔒 ADMIN = read-only mutlak
            'roleKey'    => 'admin',
            'is_locked'  => true,
        ]);
    }

    public function finalize(Request $request, PerformanceEvaluation $evaluation)
    {
        // ❌ bukan SKT
        abort_if(strtoupper($evaluation->period?->type) !== 'SKT', 404);

        // ✅ hanya boleh muktamad jika PPP sudah review
        if (($evaluation->status ?? null) !== 'PPP_REVIEWED') {
            return back()->with('error', 'Hanya SKT berstatus PPP_REVIEWED boleh dimuktamadkan.');
        }

        $fromStatus = $evaluation->status;

        $evaluation->update([
            'status' => 'FINAL',
        ]);

        $fresh = $evaluation->fresh();

        $requiredAdmin = ['I', 'II', 'III'];
        $completed = [];
        foreach ($requiredAdmin as $sec) {
            if ($this->repo->isSectionComplete($sec, 'ppp', $fresh)) {
                $completed[] = $sec;
            }
        }

        $missing = [];
        foreach ($requiredAdmin as $sec) {
            if (!$this->repo->isSectionComplete($sec, 'ppp', $fresh)) {
                $missing[] = $sec;
            }
        }

        PerformanceStatusLog::create([
            'evaluation_id' => $fresh->id,
            'actor_id'      => Auth::id(),
            'actor_role'    => 'admin',
            'action'        => 'ADMIN_FINALIZE_SKT',
            'from_status'   => $fromStatus,
            'to_status'     => 'FINAL',
            'meta'          => [
                'module'             => 'SKT',
                'required_sections'  => $requiredAdmin,
                'completed_sections' => $completed,
                'missing_sections'   => $missing,
            ],
        ]);

        return redirect()
            ->route('admin.performance.skt.show', [$evaluation->id, 'bahagian' => 'I'])
            ->with('success', 'SKT berjaya dimuktamadkan.');
    }
    public function pdf(PerformanceEvaluation $evaluation)
{
    abort_if(strtoupper($evaluation->period?->type) !== 'SKT', 404);

    if (strtoupper((string)$evaluation->status) !== 'FINAL') {
        return back()->with('error', 'PDF SKT hanya boleh dijana selepas SKT dimuktamadkan.');
    }

    $evaluation->load([
        'period',
        'assignment.pydUser.staffPosition.position',
        'assignment.pydUser.staffPosition.grade',
        'assignment.pppUser',
        'logs.actor',
    ]);

    $pdf = Pdf::loadView('admin.performance.skt.pdf', [
        'evaluation' => $evaluation,
    ])->setPaper('a4', 'portrait');

    return $pdf->stream('laporan-skt-'.$evaluation->id.'.pdf');
}
}