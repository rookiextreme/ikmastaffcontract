<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PerformancePeriod;
use App\Repositories\Performance\PerformanceAssignmentRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerformanceAssignmentController extends Controller
{
    private PerformanceAssignmentRepository $repo;

    public function __construct(PerformanceAssignmentRepository $repo)
    {
        $this->repo = $repo;
    }

    private function resolveTab(Request $request): string
    {
        $tab = strtolower((string) $request->get('tab', 'lnpt'));
        return in_array($tab, ['skt', 'lnpt'], true) ? $tab : 'lnpt';
    }

    private function resolveType(string $tab): string
    {
        return $tab === 'skt' ? 'SKT' : 'LNPT';
    }

    public function index(Request $request)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        // ✅ periods ikut type (SKT/LNPT)
        $periods = PerformancePeriod::where('type', $type)
            ->orderByDesc('is_active')
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();

        // ✅ pilih active atau selected dalam type ini sahaja
        $period = $this->repo->getActiveOrSelectedPeriod(
            $request->integer('period_id') ?: null,
            $type
        );

        // ✅ FIX: elak 404 / error kalau belum ada tempoh penilaian
        if (!$period) {
            $assignments = collect();

            // dropdown user (ringkas)
            $users = User::orderBy('name')->get(['id','name']);

            $noActivePeriod = true;

            return view('admin.performance.assignments.index', compact(
                'periods',
                'period',
                'assignments',
                'users',
                'tab',
                'type',
                'noActivePeriod'
            ));
        }

        $assignments = $this->repo->listByPeriod($period->id, $type);

        // dropdown user (ringkas)
        $users = User::orderBy('name')->get(['id','name']);

        $noActivePeriod = false;

        return view('admin.performance.assignments.index', compact(
            'periods',
            'period',
            'assignments',
            'users',
            'tab',
            'type',
            'noActivePeriod'
        ));
    }

    public function store(Request $request)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        $rules = [
            'performance_period_id' => ['required','integer','exists:performance_periods,id'],
            'pyd_user_id'           => ['required','integer','exists:users,id',
                Rule::unique('performance_assignments','pyd_user_id')
                    ->where(fn($q) => $q->where('performance_period_id', $request->performance_period_id))
            ],
            'ppp_user_id' => ['required','integer','exists:users,id','different:pyd_user_id'],
        ];

        // ✅ LNPT wajib PPK, SKT tidak
        if ($type === 'LNPT') {
            $rules['ppk_user_id'] = ['required','integer','exists:users,id','different:pyd_user_id'];
        } else {
            $rules['ppk_user_id'] = ['nullable','integer','exists:users,id','different:pyd_user_id'];
        }

        $data = $request->validate($rules);

        // ✅ safety: pastikan period type sepadan dengan tab
        $period = PerformancePeriod::findOrFail($data['performance_period_id']);
        if (strtoupper((string)$period->type) !== $type) {
            return back()->withErrors(['performance_period_id' => 'Tempoh tidak sepadan dengan tab yang dipilih.'])->withInput();
        }

        // ✅ SKT: paksa tiada PPK
        if ($type === 'SKT') {
            $data['ppk_user_id'] = null;
        }

        // tambahan rule: ppp != ppk (kekal, untuk LNPT sahaja)
        if (!empty($data['ppp_user_id']) && !empty($data['ppk_user_id']) && ((int)$data['ppp_user_id'] === (int)$data['ppk_user_id'])) {
            return back()->withErrors(['ppk_user_id' => 'PPK tidak boleh sama dengan PPP.'])->withInput();
        }

        $this->repo->store($data);

        return redirect()->route('admin.performance.assignments.index', [
                'tab' => $tab,
                'period_id' => $data['performance_period_id']
            ])
            ->with('success', $type === 'SKT'
                ? 'Lantikan PPP (SKT) berjaya disimpan.'
                : 'Lantikan PPP/PPK berjaya disimpan.'
            );
    }

    public function update(Request $request, $id)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        // SKT: hanya benarkan update PPP sahaja (PPK paksa null)
        if ($type === 'SKT') {
            $data = $request->validate([
                'ppp_user_id' => ['required','integer','exists:users,id'],
            ]);

            $data['ppk_user_id'] = null;

            $this->repo->update((int)$id, $data);

            return back()->with('success', 'Lantikan PPP (SKT) berjaya dikemaskini.');
        }

        // LNPT (asal)
        $data = $request->validate([
            'ppp_user_id' => ['nullable','integer','exists:users,id'],
            'ppk_user_id' => ['nullable','integer','exists:users,id'],
        ]);

        if (!empty($data['ppp_user_id']) && !empty($data['ppk_user_id']) && ((int)$data['ppp_user_id'] === (int)$data['ppk_user_id'])) {
            return back()->withErrors(['ppk_user_id' => 'PPK tidak boleh sama dengan PPP.']);
        }

        $this->repo->update((int)$id, $data);

        return back()->with('success', 'Lantikan berjaya dikemaskini.');
    }

    public function destroy(Request $request, $id)
    {
        $this->repo->destroy((int)$id);

        return back()->with('success', 'Lantikan berjaya dipadam.');
    }
}