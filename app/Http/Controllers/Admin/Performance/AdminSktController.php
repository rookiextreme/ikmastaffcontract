<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use Illuminate\Http\Request;

class AdminSktController extends Controller
{
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
}