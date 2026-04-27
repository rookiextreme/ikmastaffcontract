<?php

namespace App\Http\Controllers\Staff\Performance;

use App\Models\PerformanceStatusLog;
use App\Http\Controllers\Controller;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffSktController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Papar SKT (PYD)
     */
    public function show(Request $request)
    {
        $userId   = Auth::id();
        $bahagian = strtoupper((string)$request->get('bahagian', 'I'));
        $baseUrl  = route('staff.performance.skt');

        $period = $this->repo->getActivePeriod('SKT');

        if (!$period) {
            return view('performance.skt.show', [
                'period'     => null,
                'assignment' => null,
                'evaluation' => null,
                'bahagian'   => $bahagian,
                'baseUrl'    => $baseUrl,
                'message'    => 'Tiada Tempoh SKT yang aktif.',
                'roleKey'    => 'pyd',
            ]);
        }

        $assignment = $this->repo->getAssignmentForPyd($period->id, $userId);

        if (!$assignment) {
            return view('performance.skt.show', [
                'period'     => $period,
                'assignment' => null,
                'evaluation' => null,
                'bahagian'   => $bahagian,
                'baseUrl'    => $baseUrl,
                'message'    => 'Tiada lantikan SKT untuk anda.',
                'roleKey'    => 'pyd',
            ]);
        }

        $evaluation = $this->repo->getEvaluation($period->id, $assignment, $userId);

        if (!$evaluation) {
            return view('performance.skt.show', [
                'period'     => $period,
                'assignment' => $assignment,
                'evaluation' => null,
                'bahagian'   => $bahagian,
                'baseUrl'    => $baseUrl,
                'message'    => 'SKT belum dijana oleh Admin.',
                'roleKey'    => 'pyd',
            ]);
        }

        return view('performance.skt.show', [
            'period'     => $period,
            'assignment' => $assignment,
            'evaluation' => $evaluation,
            'bahagian'   => $bahagian,
            'baseUrl'    => $baseUrl,
            'message'    => null,
            'roleKey'    => 'pyd',
        ]);
    }

    /**
     * Simpan draf ikut bahagian
     */
    public function saveDraft(Request $request)
    {
        $userId   = Auth::id();
        $bahagian = strtoupper((string)$request->get('bahagian', 'I'));

        $period = $this->repo->getActivePeriod('SKT');
        if (!$period) abort(404);

        $assignment = $this->repo->getAssignmentForPyd($period->id, $userId);
        if (!$assignment) abort(403);

        $evaluation = $this->repo->ensureEvaluationExistsOrFail($period->id, $assignment, $userId);

        // ❌ bila dah submit → tak boleh edit
        if (($evaluation->status ?? 'DRAFT') !== 'DRAFT') {
            return back()->with('error', 'SKT telah dihantar kepada PPP.');
        }

        if ($bahagian === 'I') {
            $payload = $this->normalizeRows(
                $request->input('skt_bahagian_i.items', []),
                ['aktiviti','petunjuk']
            );

            $evaluation->update([
                'skt_bahagian_i' => ['items' => $payload],
            ]);
        }

        if ($bahagian === 'II') {
            $evaluation->update([
                'skt_bahagian_ii' => [
                    'tambah' => $this->normalizeRows($request->input('skt_bahagian_ii.tambah', []), ['aktiviti','petunjuk']),
                    'gugur'  => $this->normalizeRows($request->input('skt_bahagian_ii.gugur', []), ['aktiviti']),
                ],
            ]);
        }

        if ($bahagian === 'III') {
            $evaluation->update([
                'skt_bahagian_iii' => [
                    'ulasan_pyd' => $request->input('skt_bahagian_iii.ulasan_pyd'),
                    'ulasan_ppp' => $evaluation->skt_bahagian_iii['ulasan_ppp'] ?? null,
                ],
            ]);
        }

        return back()->with('success', 'Draf SKT disimpan.');
    }

    /**
     * Hantar kepada PPP
     * ✅ FIX: WAJIB lengkap Bahagian I & II(Tambah). Gugur optional.
     */
    public function submit()
    {
        $userId = Auth::id();

        $period = $this->repo->getActivePeriod('SKT');
        if (!$period) abort(404);

        $assignment = $this->repo->getAssignmentForPyd($period->id, $userId);
        if (!$assignment) abort(403);

        $evaluation = $this->repo->ensureEvaluationExistsOrFail($period->id, $assignment, $userId);

        if (($evaluation->status ?? 'DRAFT') !== 'DRAFT') {
            return back()->with('error', 'SKT telah dihantar.');
        }

        $itemsI = (array)($evaluation->skt_bahagian_i['items'] ?? []);
        $tambah = (array)($evaluation->skt_bahagian_ii['tambah'] ?? []);
        $gugur  = (array)($evaluation->skt_bahagian_ii['gugur'] ?? []);

        // ✅ Bahagian I wajib: sekurang-kurangnya 1 row lengkap
        $validI = $this->hasAtLeastOneCompleteRow($itemsI, ['aktiviti','petunjuk']);
        if (!$validI) {
            return back()->with('error', 'Lengkapkan Bahagian I dahulu (sekurang-kurangnya 1 aktiviti & petunjuk prestasi).');
        }

        // ✅ Bahagian II (Tambah) wajib
        $validTambah = $this->hasAtLeastOneCompleteRow($tambah, ['aktiviti','petunjuk']);
        if (!$validTambah) {
            return back()->with('error', 'Lengkapkan Bahagian II (Aktiviti/Projek Yang Ditambah) dahulu.');
        }

        /**
         * ✅ Bahagian II (Gugur) OPTIONAL
         * - jika kosong: OK
         * - jika ada data: mesti ada sekurang-kurangnya 1 row lengkap (aktiviti)
         */
        $validGugur = $this->isOptionalBlockValid($gugur, ['aktiviti']);
        if (!$validGugur) {
            return back()->with('error', 'Bahagian II (Aktiviti/Projek Yang Digugurkan) tidak lengkap. Jika diisi, pastikan medan Aktiviti/Projek diisi.');
        }

        $evaluation->update([
            'status'           => 'SUBMITTED',
            'skt_submitted_at' => now(),
        ]);

$fresh = $evaluation->fresh();

$requiredPYD = ['I', 'II'];
$completed = [];
foreach ($requiredPYD as $sec) {
    if ($this->repo->isSectionComplete($sec, 'pyd', $fresh)) {
        $completed[] = $sec;
    }
}

$missing = [];
foreach ($requiredPYD as $sec) {
    if (!$this->repo->isSectionComplete($sec, 'pyd', $fresh)) {
        $missing[] = $sec;
    }
}

PerformanceStatusLog::create([
    'evaluation_id' => $fresh->id,
    'actor_id'      => $userId,
    'actor_role'    => 'pyd',
    'action'        => 'SUBMIT_PYD_SKT',
    'from_status'   => 'DRAFT',
    'to_status'     => 'SUBMITTED',
    'meta'          => [
        'module'             => 'SKT',
        'required_sections'  => $requiredPYD,
        'completed_sections' => $completed,
        'missing_sections'   => $missing,
    ],
]);

        return back()->with('success', 'SKT berjaya dihantar kepada PPP.');
    }

    /**
     * ✅ OPTIONAL:
     * Tarik balik SKT jika tersubmit, supaya PYD boleh edit semula.
     */
    public function withdraw()
    {
        $userId = Auth::id();

        $period = $this->repo->getActivePeriod('SKT');
        if (!$period) abort(404);

        $assignment = $this->repo->getAssignmentForPyd($period->id, $userId);
        if (!$assignment) abort(403);

        $evaluation = $this->repo->ensureEvaluationExistsOrFail($period->id, $assignment, $userId);

        if (($evaluation->status ?? 'DRAFT') !== 'SUBMITTED') {
            return back()->with('error', 'Tidak boleh tarik balik pada status ini.');
        }

        $evaluation->update([
            'status'           => 'DRAFT',
            'skt_submitted_at' => null,
        ]);

        return back()->with('success', 'SKT berjaya ditarik balik. Anda boleh kemaskini semula.');
    }

    private function normalizeRows(array $rows, array $keys): array
    {
        return collect($rows)->map(function ($r) use ($keys) {
            $row = [];
            foreach ($keys as $k) {
                $row[$k] = trim((string)($r[$k] ?? ''));
            }
            return collect($row)->filter()->isEmpty() ? null : $row;
        })->filter()->values()->all();
    }

    /**
     * ✅ Helper: check sekurang-kurangnya 1 row lengkap ikut field wajib
     */
    private function hasAtLeastOneCompleteRow(array $rows, array $requiredKeys): bool
    {
        return collect($rows)->filter(function ($r) use ($requiredKeys) {
            foreach ($requiredKeys as $k) {
                if (trim((string)($r[$k] ?? '')) === '') return false;
            }
            return true;
        })->count() > 0;
    }

    /**
     * ✅ Helper: block OPTIONAL
     * - kalau kosong terus OK
     * - kalau ada isi, mesti ada sekurang-kurangnya 1 row lengkap
     */
    private function isOptionalBlockValid(array $rows, array $requiredKeys): bool
    {
        // rows dari DB biasanya dah dinormalize (tiada row kosong).
        // Kalau betul-betul kosong: OK
        if (count($rows) === 0) return true;

        // Kalau ada rows, mesti ada at least 1 lengkap
        return $this->hasAtLeastOneCompleteRow($rows, $requiredKeys);
    }
}