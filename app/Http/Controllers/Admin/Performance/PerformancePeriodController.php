<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Repositories\Performance\PerformancePeriodRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerformancePeriodController extends Controller
{
    private PerformancePeriodRepository $repo;

    public function __construct(PerformancePeriodRepository $repo)
    {
        $this->repo = $repo;
    }

    private function resolveTab(Request $request): string
    {
        $tab = strtolower((string)$request->get('tab', 'lnpt'));
        return in_array($tab, ['skt','lnpt'], true) ? $tab : 'lnpt';
    }

    private function resolveType(string $tab): string
    {
        return $tab === 'skt' ? 'SKT' : 'LNPT';
    }

    public function index(Request $request)
{
    $tab  = $this->resolveTab($request);
    $type = $this->resolveType($tab);

    $periods = $this->repo->list($type);

    // Tahun paling terkini yang sudah mempunyai lantikan
    $latestAssignmentYear = $periods
        ->where('assignments_count', '>', 0)
        ->max('year');

    return view(
        'admin.performance.periods.index',
        compact('periods', 'tab', 'type', 'latestAssignmentYear')
    );
}

    public function store(Request $request)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        $data = $request->validate([
            'year'       => ['required','integer','min:2000','max:2100',
                // ✅ unique ikut year+type+session
                Rule::unique('performance_periods','year')->where(function ($q) use ($type, $request) {
                    $q->where('type', $type);

                    // session boleh null
                    if ($request->filled('session')) {
                        $q->where('session', (int)$request->session);
                    } else {
                        $q->whereNull('session');
                    }
                }),
            ],
            'session'    => ['nullable','integer','in:1,2'], // optional sekarang
            'start_date' => ['required','date'],
            'end_date'   => ['required','date','after_or_equal:start_date'],
            'note'       => ['nullable','string','max:1000'],
            'is_active'  => ['nullable'], // checkbox (jangan strict boolean sebab checkbox kadang2 string)
        ]);

        // ✅ set type ikut tab
        $data['type'] = $type;

        // ✅ normalize checkbox
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $this->repo->store($data);

        return redirect()->route('admin.performance.periods.index', ['tab' => $tab])
            ->with('success', "Tempoh {$type} berjaya ditambah.");
    }

    public function update(Request $request, $id)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);
        $period = \App\Models\PerformancePeriod::findOrFail($id);

$hasAssignments = \App\Models\PerformanceAssignment::where(
    'performance_period_id',
    $period->id
)->exists();

if (
    $hasAssignments &&
    (int) $request->year !== (int) $period->year
) {
    return redirect()
        ->route('admin.performance.periods.index', ['tab' => $tab])
        ->with(
            'warning',
            'Tahun tidak boleh diubah kerana tempoh ini telah mempunyai lantikan.'
        );
}

        $data = $request->validate([
            'year'       => ['required','integer','min:2000','max:2100',
                Rule::unique('performance_periods','year')->ignore($id)->where(function ($q) use ($type, $request) {
                    $q->where('type', $type);

                    if ($request->filled('session')) {
                        $q->where('session', (int)$request->session);
                    } else {
                        $q->whereNull('session');
                    }
                }),
            ],
            'session'    => ['nullable','integer','in:1,2'],
            'start_date' => ['required','date'],
            'end_date'   => ['required','date','after_or_equal:start_date'],
            'note'       => ['nullable','string','max:1000'],
            'is_active'  => ['nullable'],
        ]);

        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        // ⚠️ type jangan benarkan update dari form biasa (selamat)
        // repo update pun dah protect.
        $this->repo->update((int)$id, $data);

        return redirect()->route('admin.performance.periods.index', ['tab' => $tab])
            ->with('success', "Tempoh {$type} berjaya dikemaskini.");
    }

    public function activate(Request $request, $id)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        $this->repo->activate((int)$id);

        return redirect()->route('admin.performance.periods.index', ['tab' => $tab])
            ->with('success', "Tempoh {$type} telah diaktifkan.");
    }
    public function cloneAssignments(Request $request, $id)
{
    $tab  = $this->resolveTab($request);
    $type = $this->resolveType($tab);

    $result = $this->repo->clonePreviousAssignments((int)$id);

    if (!$result['success']) {
        return redirect()
            ->route('admin.performance.periods.index', ['tab' => $tab])
            ->with('warning', $result['message']);
    }

    return redirect()
        ->route('admin.performance.periods.index', ['tab' => $tab])
        ->with('success', $result['message']);
}
}