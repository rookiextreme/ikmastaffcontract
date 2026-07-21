<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceCompetencyItem;
use App\Models\PerformanceEvaluation;
use App\Models\PerformancePeriod;

class PerformanceEvaluationRepository
{
    // =========================
    // PERIOD / ASSIGNMENT (ikut projek kamu)
    // =========================
    public function getActivePeriod(string $type = 'LNPT')
    {
        return PerformancePeriod::where('type', strtoupper($type))
            ->where('is_active', 1)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();
    }

    public function getAssignmentForPyd($periodId, $userId)
    {
        return \App\Models\PerformanceAssignment::with([
                'pydUser',
                'pppUser',
                'ppkUser',
            ])
            ->where('performance_period_id', $periodId)
            ->where('pyd_user_id', $userId)
            ->first();
    }

    /**
     * ✅ BARU: GET sahaja (TIDAK CREATE)
     * Digunakan oleh Staff/PPP/PPK supaya ikut flow:
     * Admin kena jana dulu → baru pengguna nampak.
     */
    public function getEvaluation($periodId, $assignment, $userId)
    {
        return \App\Models\PerformanceEvaluation::where('performance_period_id', $periodId)
            ->where('assignment_id', $assignment->id)
            ->where('pyd_user_id', $userId)
            ->first();
    }

    /**
     * ✅ KEKAL (BACKWARD COMPATIBLE) tapi dianggap ADMIN sahaja
     * - Jangan guna pada Staff/PPP/PPK jika mahu flow "Admin wajib jana".
     */
    public function getOrCreateEvaluation($periodId, $assignment, $userId)
    {
        return \App\Models\PerformanceEvaluation::firstOrCreate(
            [
                'performance_period_id' => $periodId,
                'assignment_id'         => $assignment->id,
                'pyd_user_id'           => $userId,
            ],
            [
                'status' => 'DRAFT',
            ]
        );
    }

    /**
     * ✅ Optional helper: pastikan evaluation memang sudah dijana admin.
     * Kalau tiada, terus bagi error (sesuai untuk save/submit di staff).
     */
    public function ensureEvaluationExistsOrFail($periodId, $assignment, $userId): PerformanceEvaluation
    {
        $eval = $this->getEvaluation($periodId, $assignment, $userId);

        if (!$eval) {
            abort(404, 'Penilaian belum dijana oleh Admin.');
        }

        return $eval;
    }

    // =========================
    // ✅ ITEM KOMPETENSI (LNPT)
    // =========================
    public function getCompetencyItems()
    {
        return PerformanceCompetencyItem::where('is_active', 1)
            ->orderBy('code')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getCompetencyItemsByCode(
    string $code,
    ?string $pydGroup = null
) {
    $code = strtoupper(trim($code));
    $pydGroup = strtoupper(trim((string) $pydGroup));

    $query = PerformanceCompetencyItem::where('code', $code)
        ->where('is_active', 1);

    /*
    |--------------------------------------------------------------------------
    | Bahagian V ikut kumpulan perkhidmatan PYD
    |--------------------------------------------------------------------------
    |
    | A:
    | - item ALL
    | - item A
    |
    | BC:
    | - item ALL sahaja
    |
    | Bahagian lain:
    | - kekalkan flow asal
    |
    */

    if ($code === 'V') {
        if ($pydGroup === 'A') {
            $query->whereIn('group_type', [
                'ALL',
                'A',
            ]);
        } else {
            /*
             * BC dan rekod lama yang belum mempunyai kumpulan
             * akan menggunakan item umum sahaja.
             */
            $query->where('group_type', 'ALL');
        }
    }

    return $query
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
}

    // ✅ tajuk penuh LNPT + wajaran
    public function sectionMeta(): array
    {
        return [
            'I'    => ['title' => 'Maklumat Penilaian', 'weight' => null],
            'II'   => ['title' => 'Kegiatan & Latihan', 'weight' => null],
            'III'  => ['title' => 'Penghasilan Kerja', 'weight' => 50],
            'IV'   => ['title' => 'Pengetahuan & Kemahiran', 'weight' => 25],
            'V'    => ['title' => 'Kualiti Peribadi', 'weight' => 20],
            'VI'   => ['title' => 'Kegiatan & Sumbangan di Luar Tugas Rasmi', 'weight' => 5],
            'VII'  => ['title' => 'Jumlah Markah Keseluruhan', 'weight' => null],
            'VIII' => ['title' => 'Ulasan & Pengesahan PPP', 'weight' => null],
            'IX'   => ['title' => 'Ulasan Keseluruhan oleh PPK', 'weight' => null],
        ];
    }

    public function saveDraft(PerformanceEvaluation $eval, array $data, int $userId): void
    {
        // ✅ kekalkan legacy (kalau masih digunakan mana-mana tempat)
        $eval->update([
            'pyd_summary' => $data['pyd_summary'] ?? $eval->pyd_summary,
            'pyd_remarks' => $data['pyd_remarks'] ?? $eval->pyd_remarks,
        ]);
    }

    public function submit(PerformanceEvaluation $eval, array $data, int $userId): void
    {
        // PYD submit -> SUBMITTED
        $eval->update([
            'status'       => 'SUBMITTED',
            'submitted_at' => now(),
        ]);
    }

    // =========================
    // ✅ SECTION RULES
    // =========================
    public function requiredSections(string $roleKey): array
    {
        return match ($roleKey) {
            'pyd'   => ['I','II'],
            'ppp'   => ['III','IV','V','VI','VIII'], // VII auto
            'ppk'   => ['III','IV','V','VI','IX'],
            'admin' => [],
            default => [],
        };
    }

    /**
     * Helper: kira jumlah score ikut section (III/IV/V/VI) berdasarkan code item
     * - kalau relation item tak load, dia fallback kira semua (min 1)
     */
    private function countScoresBySectionCode(PerformanceEvaluation $evaluation, string $roleKey, string $code): int
    {
        $evaluation->loadMissing(['competencyScores.item']);

        // pilih field ikut role
        $scoreField = ($roleKey === 'ppk') ? 'ppk_score' : 'ppp_score';

        $scores = $evaluation->competencyScores->whereNotNull($scoreField);

        // kalau item wujud, tapis ikut code
        $withItem = $scores->filter(function ($sc) {
            return !empty($sc->item);
        });

        if ($withItem->count() > 0) {
            return $withItem->filter(function ($sc) use ($code) {
                return strtoupper((string)($sc->item->code ?? '')) === strtoupper($code);
            })->count();
        }

        // fallback: minimum 1 markah overall
        return $scores->count();
    }

    // =========================================================
    // ✅ BARU (FIX): SECTION III–VI mesti lengkap SEMUA item
    // =========================================================
    private function requiredItemCountByCode(
    string $code,
    ?string $pydGroup = null
): int {
    $code = strtoupper(trim($code));
    $pydGroup = strtoupper(trim((string) $pydGroup));

    $query = PerformanceCompetencyItem::where('is_active', 1)
        ->where('code', $code);

    if ($code === 'V') {
        if ($pydGroup === 'A') {
            $query->whereIn('group_type', [
                'ALL',
                'A',
            ]);
        } else {
            $query->where('group_type', 'ALL');
        }
    }

    return $query->count();
}

    private function filledScoreCountByCode(
    PerformanceEvaluation $evaluation,
    string $roleKey,
    string $code,
    ?string $pydGroup = null
): int {
    $evaluation->loadMissing([
        'competencyScores.item',
    ]);

    $scoreField = $roleKey === 'ppk'
        ? 'ppk_score'
        : 'ppp_score';

    $code = strtoupper(trim($code));
    $pydGroup = strtoupper(trim((string) $pydGroup));

    return $evaluation->competencyScores
        ->filter(function ($score) use (
            $scoreField,
            $code,
            $pydGroup
        ) {
            if (empty($score->item)) {
                return false;
            }

            $itemCode = strtoupper(
                trim((string) ($score->item->code ?? ''))
            );

            if ($itemCode !== $code) {
                return false;
            }

            /*
             * Untuk Bahagian V, pastikan hanya item kumpulan
             * berkenaan dikira.
             */
            if ($code === 'V') {
                $groupType = strtoupper(
                    trim((string) ($score->item->group_type ?? 'ALL'))
                );

                if ($pydGroup === 'A') {
                    if (!in_array($groupType, ['ALL', 'A'], true)) {
                        return false;
                    }
                } else {
                    if ($groupType !== 'ALL') {
                        return false;
                    }
                }
            }

            return $score->{$scoreField} !== null;
        })
        ->count();
}

    // =========================================================
    // ✅ TAMBAH: Helper untuk SKT (row lengkap & optional block)
    // =========================================================
    private function hasAtLeastOneCompleteRow(array $rows, array $requiredKeys): bool
    {
        $rows = is_array($rows) ? $rows : [];

        return collect($rows)->filter(function ($r) use ($requiredKeys) {
            foreach ($requiredKeys as $k) {
                if (trim((string)($r[$k] ?? '')) === '') return false;
            }
            return true;
        })->count() > 0;
    }

    private function isOptionalBlockValid(array $rows, array $requiredKeys): bool
    {
        $rows = is_array($rows) ? $rows : [];

        // kosong = OK
        if (count($rows) === 0) return true;

        // kalau ada rows, mesti ada sekurang-kurangnya 1 lengkap
        return $this->hasAtLeastOneCompleteRow($rows, $requiredKeys);
    }

    public function isSectionComplete(string $section, string $roleKey, PerformanceEvaluation $evaluation): bool
    {
        // ✅ tambah period untuk detect SKT/LNPT
        $evaluation->loadMissing([
    'competencyScores.item',
    'period',
    'assignment',
]);

        // =========================
        // ✅ RULE: SKT (untuk tanda ✓ tab SKT)
        // =========================
        $type  = strtoupper(trim((string)($evaluation->period->type ?? '')));
        $isSkt = ($type === 'SKT') || !empty($evaluation->skt_bahagian_i) || !empty($evaluation->skt_bahagian_ii);

        if ($isSkt) {

            // ambil data json skt
            $iItems    = (array)($evaluation->skt_bahagian_i['items'] ?? []);
            $tambah    = (array)($evaluation->skt_bahagian_ii['tambah'] ?? []);
            $gugur     = (array)($evaluation->skt_bahagian_ii['gugur'] ?? []);
            $ulasanPyd = (string)($evaluation->skt_bahagian_iii['ulasan_pyd'] ?? '');

            return match ($section) {
                // SKT I: wajib ada sekurang-kurangnya 1 row lengkap
                'I' => $this->hasAtLeastOneCompleteRow($iItems, ['aktiviti','petunjuk']),

                /**
                 * ✅ SKT II: IKUT LOGIC VIEW II.blade.php
                 * complete jika:
                 *  - ada sekurang-kurangnya 1 item (Ditambah atau Digugurkan)
                 *  - dan jika ada item Ditambah yang diisi, aktiviti & petunjuk mesti penuh (tiada separuh)
                 */
                'II' => (function () use ($tambah, $gugur) {

                    $filledTambah = 0;
                    $okTambah = true;

                    foreach ($tambah as $r) {
                        $a = trim((string)($r['aktiviti'] ?? ''));
                        $p = trim((string)($r['petunjuk'] ?? ''));

                        if ($a === '' && $p === '') continue;

                        $filledTambah++;

                        if ($a === '' || $p === '') {
                            $okTambah = false;
                            break;
                        }
                    }

                    $filledGugur = 0;
                    foreach ($gugur as $r) {
                        $a = trim((string)($r['aktiviti'] ?? ''));
                        if ($a === '') continue;
                        $filledGugur++;
                    }

                    return (($filledTambah + $filledGugur) > 0) && $okTambah;

                })(),

                // SKT III: ulasan pyd (kalau awak nak wajib)
                'III' => (mb_strlen(trim($ulasanPyd)) >= 3),

                default => false,
            };
        }
        /*
|--------------------------------------------------------------------------
| LNPT: Kumpulan perkhidmatan PYD
|--------------------------------------------------------------------------
*/
$pydGroup = strtoupper(
    trim((string) ($evaluation->assignment->pyd_group ?? 'BC'))
);

        // ✅ ikut role: PPP kira ppp_score, PPK kira ppk_score
        $pppScoreCount = $evaluation->competencyScores->whereNotNull('ppp_score')->count();
        $ppkScoreCount = $evaluation->competencyScores->whereNotNull('ppk_score')->count();

        return match ($section) {
            // I: info asas wujud
            'I' => !empty($evaluation->assignment_id) && !empty($evaluation->performance_period_id),

            /**
             * ✅ II: guna JSON bahagian_ii_data (bukan pyd_summary)
             * complete jika ada sekurang-kurangnya 1 row data dalam mana-mana blok
             */
            'II' => !empty($evaluation->bahagian_ii_data)
                && (
                    !empty($evaluation->bahagian_ii_data['kegiatan'])
                    || !empty($evaluation->bahagian_ii_data['latihan_hadir'])
                    || !empty($evaluation->bahagian_ii_data['latihan_perlu'])
                ),

            /**
             * ✅ FIX III–VI (STRICT):
             * - mesti isi markah untuk SEMUA item dalam section itu
             * - PPP guna ppp_score, PPK guna ppk_score
             */
            'III' => $this->filledScoreCountByCode(
    $evaluation,
    $roleKey,
    'III'
) >= $this->requiredItemCountByCode('III'),

'IV' => $this->filledScoreCountByCode(
    $evaluation,
    $roleKey,
    'IV'
) >= $this->requiredItemCountByCode('IV'),

'V' => $this->filledScoreCountByCode(
    $evaluation,
    $roleKey,
    'V',
    $pydGroup
) >= $this->requiredItemCountByCode(
    'V',
    $pydGroup
),

'VI' => $this->filledScoreCountByCode(
    $evaluation,
    $roleKey,
    'VI'
) >= $this->requiredItemCountByCode('VI'),

            // VII auto
            'VII' => true,

            /**
             * ✅ VIII (PPP) – ikut UI baru
             * Wajib: tahun + bulan + 2 ulasan (min 3 aksara)
             * (Kalau awak nak tahun/bulan tidak wajib, boleh buang 2 syarat pertama)
             */
            'VIII' => (
                $roleKey === 'ppp'
                    ? (
                        $evaluation->ppp_supervise_years !== null
                        && $evaluation->ppp_supervise_months !== null
                        && filled($evaluation->ppp_overall_performance)
                        && mb_strlen(trim((string)$evaluation->ppp_overall_performance)) >= 3
                        && filled($evaluation->ppp_career_progress)
                        && mb_strlen(trim((string)$evaluation->ppp_career_progress)) >= 3
                    )
                    // fallback legacy (kalau ada role lain tengok VIII)
                    : (filled($evaluation->ppp_comment) && mb_strlen(trim((string)$evaluation->ppp_comment)) >= 3)
            ),

            /**
             * ✅ IX (PPK) – ikut UI baru (kalau awak dah tambah fields ppk_supervise_years/months)
             * Kalau belum ada kolum tu, buang syarat tahun/bulan dan tinggal ulasan sahaja.
             */
            'IX' => (
                $roleKey === 'ppk'
                    ? (
                        filled($evaluation->ppk_comment)
                        && mb_strlen(trim((string)$evaluation->ppk_comment)) >= 3
                    )
                    : (filled($evaluation->ppk_comment) && mb_strlen(trim((string)$evaluation->ppk_comment)) >= 3)
            ),

            default => false,
        };
    }

    public function allRequiredComplete(string $roleKey, PerformanceEvaluation $evaluation): bool
    {
        foreach ($this->requiredSections($roleKey) as $sec) {
            if (!$this->isSectionComplete($sec, $roleKey, $evaluation)) return false;
        }
        return true;
    }

    public function incompleteRequiredSections(string $roleKey, PerformanceEvaluation $evaluation): array
    {
        $missing = [];
        foreach ($this->requiredSections($roleKey) as $sec) {
            if (!$this->isSectionComplete($sec, $roleKey, $evaluation)) $missing[] = $sec;
        }
        return $missing;
    }

    // =========================================================
    // ✅ TAMBAH BARU: FORMULA BERWAJARAN (SUPAYA MAX 100)
    // =========================================================

    /**
     * ✅ KIRA MARKAH FINAL BERWAJARAN UNTUK PPP/PPK (maks 100)
     *
     * Formula setiap bahagian:
     *   weighted = (raw_total / raw_max) * weight
     *
     * - raw_total: jumlah score (ppp_score/ppk_score) bagi item dalam code itu
     * - raw_max  : jumlah max_score item dalam code itu (default 10 jika tiada)
     * - weight   : dari sectionMeta() (III 50, IV 25, V 20, VI 5)
     */
    public function computeWeightedFinalScore(PerformanceEvaluation $evaluation, string $roleKey): float
    {
        $roleKey = strtolower(trim($roleKey));

        $scoreField = match ($roleKey) {
            'ppp' => 'ppp_score',
            'ppk' => 'ppk_score',
            default => null,
        };

        if (!$scoreField) return 0.0;

        // pastikan relation item ada
        $evaluation->loadMissing(['competencyScores.item']);

        $meta = $this->sectionMeta();
        $codes = ['III','IV','V','VI'];

        $final = 0.0;

        foreach ($codes as $code) {
            $weight = (float)($meta[$code]['weight'] ?? 0);

            $rawTotal = 0.0;
            $rawMax   = 0.0;

            foreach ($evaluation->competencyScores as $sc) {
                if (empty($sc->item)) continue;

                $itemCode = strtoupper((string)($sc->item->code ?? ''));
                if ($itemCode !== strtoupper($code)) continue;

                // max_score default 10
                $max = (float)($sc->item->max_score ?? $sc->item->max_mark ?? 10);
                $rawMax += $max;

                $rawTotal += (float)($sc->{$scoreField} ?? 0);
            }

            if ($rawMax <= 0 || $weight <= 0) continue;

            $final += ($rawTotal / $rawMax) * $weight;
        }

        return round($final, 2);
    }

    /**
     * (Optional) Debug breakdown untuk audit / semak kiraan.
     * Contoh output:
     * [
     *   'III' => ['raw_total'=>..,'raw_max'=>..,'weight'=>50,'weighted'=>..],
     *   ...
     * ]
     */
    public function computeWeightedBreakdown(PerformanceEvaluation $evaluation, string $roleKey): array
    {
        $roleKey = strtolower(trim($roleKey));

        $scoreField = match ($roleKey) {
            'ppp' => 'ppp_score',
            'ppk' => 'ppk_score',
            default => null,
        };

        if (!$scoreField) return [];

        $evaluation->loadMissing(['competencyScores.item']);

        $meta = $this->sectionMeta();
        $codes = ['III','IV','V','VI'];

        $out = [];

        foreach ($codes as $code) {
            $weight = (float)($meta[$code]['weight'] ?? 0);

            $rawTotal = 0.0;
            $rawMax   = 0.0;

            foreach ($evaluation->competencyScores as $sc) {
                if (empty($sc->item)) continue;

                $itemCode = strtoupper((string)($sc->item->code ?? ''));
                if ($itemCode !== strtoupper($code)) continue;

                $max = (float)($sc->item->max_score ?? $sc->item->max_mark ?? 10);
                $rawMax += $max;

                $rawTotal += (float)($sc->{$scoreField} ?? 0);
            }

            $weighted = ($rawMax > 0 && $weight > 0) ? (($rawTotal / $rawMax) * $weight) : 0;

            $out[$code] = [
                'raw_total' => round($rawTotal, 2),
                'raw_max'   => round($rawMax, 2),
                'weight'    => round($weight, 2),
                'weighted'  => round($weighted, 2),
            ];
        }

        return $out;
    }
}