<?php

namespace App\Http\Controllers\Staff\Performance;

use App\Http\Controllers\Controller;
use App\Models\PerformanceEvaluation;
use App\Repositories\Performance\PerformanceEvaluationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class StaffPerformanceController extends Controller
{
    private PerformanceEvaluationRepository $repo;

    public function __construct(PerformanceEvaluationRepository $repo)
    {
        $this->repo = $repo;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        // ✅ LNPT guna ?bahagian=I..IX (default I untuk PYD)
        $bahagian = strtoupper((string)$request->get('bahagian', 'I'));

        // ✅ meta tajuk/wajaran
        $sectionMeta = $this->repo->sectionMeta();

        $period = $this->repo->getActivePeriod();
        if (!$period) {
            return view('staff.performance.index', [
                'period' => null,
                'assignment' => null,
                'evaluation' => null,
                'items' => collect(),
                'scores' => collect(),
                'message' => 'Tiada Tempoh Penilaian Prestasi yang aktif.',
                'bahagian' => $bahagian,
                'sectionMeta' => $sectionMeta,

                // ✅ elak view error
                'required' => [],
                'completedMap' => [],
                'canSubmit' => false,
                'missingSecs' => [],
                'baseUrl' => route('staff.performance.index'),
            ]);
        }

        $assignment = $this->repo->getAssignmentForPyd($period->id, $userId);
        if (!$assignment) {
            return view('staff.performance.index', [
                'period' => $period,
                'assignment' => null,
                'evaluation' => null,
                'items' => collect(),
                'scores' => collect(),
                'message' => 'Anda belum dilantik (tiada PPP/PPK) untuk tempoh ini. Sila rujuk admin.',
                'bahagian' => $bahagian,
                'sectionMeta' => $sectionMeta,

                // ✅ elak view error
                'required' => [],
                'completedMap' => [],
                'canSubmit' => false,
                'missingSecs' => [],
                'baseUrl' => route('staff.performance.index'),
            ]);
        }

        /**
         * ✅ KEMASKINI (ikut flow Irham):
         * PYD TIDAK BOLEH auto-jana evaluation.
         * Evaluation hanya wujud jika ADMIN dah jana (bulk-generate).
         */
        $evaluation = $this->repo->getEvaluation($period->id, $assignment, $userId);

        // ✅ Kekalkan asal: default ambil semua
        $pydGroup = strtoupper(
    trim((string) ($evaluation->assignment->pyd_group ?? 'BC'))
);

$items = $this->repo->getCompetencyItems();

if (in_array($bahagian, ['III', 'IV', 'V', 'VI'], true)) {
    $items = $this->repo->getCompetencyItemsByCode(
        $bahagian,
        $bahagian === 'V' ? $pydGroup : null
    );
}

        $scores = collect();

        // ✅ untuk UI wajib section
        $roleKey = 'pyd';
        $required = $this->repo->requiredSections($roleKey);
        $sections = ['I','II','III','IV','V','VI','VII','VIII','IX'];
        $completedMap = [];
        foreach ($sections as $sec) {
            $completedMap[$sec] = false;
        }
        $canSubmit = false;
        $missingSecs = $required;

        // ✅ baseUrl untuk tab nav (supaya tak error & link stabil)
        $baseUrl = route('staff.performance.index');

        // ✅ jika evaluation wujud barulah load scores & status complete
        if ($evaluation) {
            $scores = $evaluation->competencyScores()->with('item')->get()->keyBy('competency_item_id');

            foreach ($sections as $sec) {
                $completedMap[$sec] = $this->repo->isSectionComplete($sec, $roleKey, $evaluation);
            }
            $canSubmit = $this->repo->allRequiredComplete($roleKey, $evaluation);
            $missingSecs = $this->repo->incompleteRequiredSections($roleKey, $evaluation);
        }

        // ✅ mesej bila belum dijana admin
        $message = null;
        if (!$evaluation) {
            $message = 'Penilaian Prestasi belum dijana oleh Admin untuk tempoh ini. Sila tunggu / rujuk Admin.';
            // bila belum ada evaluation, kosongkan items/scores supaya UI tak bagi edit
            $items = collect();
            $scores = collect();
        }

        return view('staff.performance.index', compact(
            'period','assignment','evaluation','items','scores',
            'required','completedMap','canSubmit','missingSecs',
            'bahagian','sectionMeta','baseUrl','message'
        ));
    }

    /**
     * ✅ 1 endpoint untuk SAVE + SUBMIT (ikut button action)
     * Button save:   name="action" value="save"
     * Button submit: name="action" value="submit"
     */
    public function saveDraft(Request $request)
    {
        $userId = Auth::id();
        $action = $request->input('action', 'save');

        $rules = [
            'evaluation_id' => ['required', 'integer', 'exists:performance_evaluations,id'],

            // ✅ BAHAGIAN II (JSON)
            'bahagian_ii' => ['nullable', 'array'],

            'bahagian_ii.kegiatan' => ['nullable', 'array'],
            'bahagian_ii.kegiatan.*.aktiviti' => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.kegiatan.*.peringkat' => ['nullable', 'string', 'max:5000'],

            'bahagian_ii.latihan_hadir' => ['nullable', 'array'],
            'bahagian_ii.latihan_hadir.*.nama' => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.latihan_hadir.*.tarikh' => ['nullable', 'string', 'max:200'],
            'bahagian_ii.latihan_hadir.*.tempat' => ['nullable', 'string', 'max:5000'],

            'bahagian_ii.latihan_perlu' => ['nullable', 'array'],
            'bahagian_ii.latihan_perlu.*.bidang' => ['nullable', 'string', 'max:5000'],
            'bahagian_ii.latihan_perlu.*.sebab' => ['nullable', 'string', 'max:5000'],

            // (legacy lama)
            'pyd_summary'   => ['nullable', 'string', 'max:5000'],
            'pyd_remarks'   => ['nullable', 'string', 'max:5000'],
            'action'        => ['nullable', 'in:save,submit'],
        ];

        $data = $request->validate($rules);

        $eval = PerformanceEvaluation::findOrFail($data['evaluation_id']);

        if ((int)$eval->pyd_user_id !== (int)$userId) abort(403);

        // lock bila bukan DRAFT
        if ($eval->status !== 'DRAFT') {
            return back()->with('error', 'Status tidak membenarkan kemaskini.');
        }

        /**
         * =========================================================
         * ✅ SIMPAN BAHAGIAN II (JSON)
         * - buang row kosong supaya data kemas
         * =========================================================
         */
        $bahagianII = $data['bahagian_ii'] ?? null;

        if (is_array($bahagianII)) {

            $bahagianII['kegiatan'] = array_values(array_filter($bahagianII['kegiatan'] ?? [], function ($r) {
                $a = trim((string)($r['aktiviti'] ?? ''));
                $p = trim((string)($r['peringkat'] ?? ''));
                return $a !== '' || $p !== '';
            }));

            $bahagianII['latihan_hadir'] = array_values(array_filter($bahagianII['latihan_hadir'] ?? [], function ($r) {
                $n = trim((string)($r['nama'] ?? ''));
                $t = trim((string)($r['tarikh'] ?? ''));
                $m = trim((string)($r['tempat'] ?? ''));
                return $n !== '' || $t !== '' || $m !== '';
            }));

            $bahagianII['latihan_perlu'] = array_values(array_filter($bahagianII['latihan_perlu'] ?? [], function ($r) {
                $b = trim((string)($r['bidang'] ?? ''));
                $s = trim((string)($r['sebab'] ?? ''));
                return $b !== '' || $s !== '';
            }));

            $isAllEmpty =
                empty($bahagianII['kegiatan']) &&
                empty($bahagianII['latihan_hadir']) &&
                empty($bahagianII['latihan_perlu']);

            $eval->update([
                'bahagian_ii_data' => $isAllEmpty ? null : $bahagianII,
            ]);
        }

        /**
         * =========================================================
         * ✅ SIMPAN LEGACY FIELD (kalau repo masih guna)
         * =========================================================
         */
        $this->repo->saveDraft($eval, $data, $userId);

        // ✅ SAVE sahaja (TIADA LOG SAVE_DRAFT ikut polisi baru)
        if (($data['action'] ?? 'save') === 'save') {
            return redirect()
                ->route('staff.performance.index')
                ->with('success', 'Draf berjaya disimpan.');
        }

        /**
         * =========================================================
         * ✅ SUBMIT: wajib ada isi Bahagian II (JSON)
         * =========================================================
         */
        $fresh = $eval->fresh(); // ambil latest bahagian_ii_data

        $ii = $fresh->bahagian_ii_data;
        $isEmptySubmit = empty($ii)
            || (
                empty($ii['kegiatan'])
                && empty($ii['latihan_hadir'])
                && empty($ii['latihan_perlu'])
            );

        if ($isEmptySubmit) {
            return back()
                ->withErrors(['bahagian_ii' => 'Sila lengkapkan Bahagian II sebelum hantar kepada PPP.'])
                ->withInput();
        }

        // ✅ server-side wajib lengkap section (ikut repo)
        $missing = $this->repo->incompleteRequiredSections('pyd', $fresh);
        if (!empty($missing)) {
            return back()->with('error', 'Sila lengkapkan bahagian wajib dahulu: '.implode(', ', $missing));
        }

        // submit
        $this->repo->submit($fresh, $data, $userId);

        // ✅ LOG MASA HANTAR SAHAJA (PYD -> PPP) + DETAIL BAHAGIAN
        $fresh2 = $fresh->fresh();

        // ✅ (KEKAL ASAL) tetapi ubah cara kira completed supaya PYD tak dapat VII
        // sekarang: hanya bahagian wajib PYD (I, II)
        $requiredPYD = $this->repo->requiredSections('pyd');

        $completed = [];
        foreach ($requiredPYD as $sec) {
            if ($this->repo->isSectionComplete($sec, 'pyd', $fresh2)) {
                $completed[] = $sec;
            }
        }

        $missing2 = $this->repo->incompleteRequiredSections('pyd', $fresh2);

        \App\Models\PerformanceStatusLog::create([
            'evaluation_id' => $fresh2->id,
            'actor_id'      => $userId,
            'actor_role'    => 'pyd',
            'action'        => 'SUBMIT_PYD',
            'from_status'   => 'DRAFT',
            'to_status'     => 'SUBMITTED',
            'meta'          => [
                'required_sections'  => $requiredPYD,
                'completed_sections' => $completed,
                'missing_sections'   => $missing2,
            ],
        ]);

        // NOTIFICATION PPP
$fresh3 = \App\Models\PerformanceEvaluation::with('assignment')
    ->find($fresh->id);

if ($fresh3 && $fresh3->assignment && $fresh3->assignment->ppp_user_id) {
    NotificationHelper::send(
        $fresh3->assignment->ppp_user_id,
        'LNPT Menunggu Semakan PPP',
        Auth::user()->name.' telah menghantar LNPT untuk semakan PPP.',
        route('ppp.performance.index'),
        'LNPT',
        'info'
    );
}

        return redirect()
            ->route('staff.performance.index')
            ->with('success', 'Borang berjaya dihantar kepada PPP.');
    }

    // optional legacy endpoint
    public function submit(Request $request)
    {
           dd('MASUK FUNCTION SUBMIT');
        $userId = Auth::id();

        $data = $request->validate([
            'evaluation_id' => ['required','integer','exists:performance_evaluations,id'],
        ]);

        $eval = PerformanceEvaluation::findOrFail($data['evaluation_id']);

        if ((int)$eval->pyd_user_id !== (int)$userId) abort(403);

        $missing = $this->repo->incompleteRequiredSections('pyd', $eval);
        if (!empty($missing)) {
            return back()->with('error', 'Sila lengkapkan bahagian wajib dahulu: '.implode(', ', $missing));
        }

        if ($eval->status !== 'DRAFT') {
            return back()->with('error', 'Status tidak membenarkan hantar.');
        }

        $eval->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        // ✅ LOG MASA HANTAR SAHAJA (PYD -> PPP) + DETAIL BAHAGIAN (PYD SAHAJA)
        $fresh = $eval->fresh();

        // hanya bahagian wajib PYD (kebiasaan: I, II)
        $requiredPYD = $this->repo->requiredSections('pyd');

        $completed = [];
        foreach ($requiredPYD as $sec) {
            if ($this->repo->isSectionComplete($sec, 'pyd', $fresh)) {
                $completed[] = $sec;
            }
        }

        $missing2 = $this->repo->incompleteRequiredSections('pyd', $fresh);

        \App\Models\PerformanceStatusLog::create([
            'evaluation_id' => $fresh->id,
            'actor_id'      => $userId,
            'actor_role'    => 'pyd',
            'action'        => 'SUBMIT_PYD',
            'from_status'   => 'DRAFT',
            'to_status'     => 'SUBMITTED',
            'meta'          => [
                'required_sections'  => $requiredPYD,
                'completed_sections' => $completed,
                'missing_sections'   => $missing2,
            ],
        ]);

        return redirect()->route('staff.performance.index')->with('success', 'Borang berjaya dihantar kepada PPP.');
    }
}
