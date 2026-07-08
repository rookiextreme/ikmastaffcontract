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
    $status = $request->get('status');
    $year   = $request->get('year');
    $module = $request->get('module'); // SKT / LNPT

    $query = PerformanceEvaluation::with([
        'period',
        'assignment.pydUser',
        'assignment.pppUser',
        'assignment.ppkUser',
        'logs',
    ])->orderByDesc('id');

    if ($status) {
        $query->where('status', $status);
    }

    if ($year) {
        $query->whereHas('period', function ($q) use ($year) {
            $q->where('year', $year);
        });
    }

    if ($module === 'SKT') {
    $query->whereHas('logs', function ($q) {
        $q->where('meta->module', 'SKT');
    });
}

if ($module === 'LNPT') {
    $query->whereDoesntHave('logs', function ($q) {
        $q->where('meta->module', 'SKT');
    });
}

    $rows = $query->paginate(20)->withQueryString();

    return view('admin.performance.logs.index', compact(
        'rows',
        'status',
        'year',
        'module'
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
