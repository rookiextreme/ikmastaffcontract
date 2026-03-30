<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use Illuminate\Http\Request;

class PerformanceLogController extends Controller
{
    /**
     * Senarai penilaian (untuk audit log)
     */
    public function index(Request $request)
    {
        $status = $request->get('status'); // DRAFT / SUBMITTED / PPP_SCORED / PPK_APPROVED
        $year   = $request->get('year');   // contoh: 2026

        $query = PerformanceEvaluation::with([
            'period',
            'assignment.pydUser',
            'assignment.pppUser',
            'assignment.ppkUser',
        ])->orderByDesc('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($year) {
            $query->whereHas('period', function ($q) use ($year) {
                $q->where('year', $year);
            });
        }

        $rows = $query->paginate(20)->withQueryString();

        return view('admin.performance.logs.index', compact(
            'rows',
            'status',
            'year'
        ));
    }

    /**
     * Detail log (timeline)
     */
    public function show($evaluationId)
    {
        $evaluation = PerformanceEvaluation::with([
            'period',
            'assignment.pydUser',
            'assignment.pppUser',
            'assignment.ppkUser',
            'logs.actor', // 🔥 semua log + siapa buat
        ])->findOrFail($evaluationId);

        return view('admin.performance.logs.show', compact('evaluation'));
    }
}
