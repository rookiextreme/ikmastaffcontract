@php
    $logs = $evaluation->logs ?? collect();

    $statusLabel = [
        'DRAFT'        => 'Draf',
        'SUBMITTED'    => 'Dihantar kepada PPP',
        'PPP_SCORED'   => 'Dinilai oleh PPP',
        'PPK_APPROVED' => 'Disahkan oleh PPK',
        'FINAL'        => 'Muktamad',
    ];

    $actionText = [
        'SUBMIT_PYD' => [
            'title' => 'Penghantaran oleh PYD',
            'desc'  => 'Borang penilaian telah dihantar kepada PPP untuk penilaian.',
        ],
        'SUBMIT_PPP' => [
            'title' => 'Penilaian oleh PPP',
            'desc'  => 'PPP telah melengkapkan penilaian dan menghantar kepada PPK untuk pengesahan.',
        ],
        'APPROVE_PPK' => [
            'title' => 'Pengesahan oleh PPK',
            'desc'  => 'PPK telah membuat semakan dan mengesahkan penilaian.',
        ],
        // ✅ BARU: UPDATE_PPSM (audit)
        'UPDATE_PPSM' => [
            'title' => 'Kemaskini Markah PPSM (Admin/UPSM)',
            'desc'  => 'Markah PPSM telah dikemaskini untuk tujuan audit.',
        ],
        // ✅ BARU: ADMIN_UPDATE_PYD (audit)
        'ADMIN_UPDATE_PYD' => [
            'title' => 'Kemaskini Maklumat PYD (Admin)',
            'desc'  => 'Admin telah mengemaskini maklumat PYD tanpa mengubah status penilaian.',
        ],
    ];

    $roleLabel = [
        'pyd'   => 'PYD',
        'ppp'   => 'PPP',
        'ppk'   => 'PPK',
        'admin' => 'ADMIN',
    ];

    $roleBadgeClass = [
        'pyd'   => 'bg-light-primary text-primary border',
        'ppp'   => 'bg-light-success text-success border',
        'ppk'   => 'bg-light-warning text-warning border',
        'admin' => 'bg-light-danger text-danger fw-semibold',
    ];

    // ✅ Debug: hanya kalau admin tambah &debug=1 di URL
    $debug = request()->boolean('debug');

    // ========= helper ringkasan perubahan (mesra pengguna) =========
    $countItems = function($arr){
        return is_array($arr) ? count($arr) : 0;
    };

    // ✅ Ringkasan perubahan "natural" per field (Bahagian II)
    $summarizeChanges = function($before, $after) use ($countItems) {
        $out = [];

        $b = is_array($before) ? $before : [];
        $a = is_array($after) ? $after : [];

        $b2 = $b['bahagian_ii_data'] ?? [];
        $a2 = $a['bahagian_ii_data'] ?? [];

        // mapping label & fields untuk ringkasan
        $groups = [
            'kegiatan' => [
                'label' => 'Kegiatan',
                'fields' => [
                    'aktiviti'  => 'Aktiviti',
                    'peringkat' => 'Peringkat',
                ],
            ],
            'latihan_hadir' => [
                'label' => 'Latihan Dihadiri',
                'fields' => [
                    'nama'   => 'Nama Latihan',
                    'tarikh' => 'Tarikh/Tempoh',
                    'tempat' => 'Tempat',
                ],
            ],
            'latihan_perlu' => [
                'label' => 'Latihan Diperlukan',
                'fields' => [
                    'bidang' => 'Bidang Latihan',
                    'sebab'  => 'Sebab',
                ],
            ],
        ];

        $norm = function($v){
            $v = is_null($v) ? '' : (string)$v;
            $v = preg_replace('/\s+/', ' ', trim($v));
            return $v;
        };

        foreach ($groups as $key => $cfg) {
            $label  = $cfg['label'];
            $fields = $cfg['fields'];

            $beforeRows = is_array($b2[$key] ?? null) ? $b2[$key] : [];
            $afterRows  = is_array($a2[$key] ?? null) ? $a2[$key] : [];

            $bCount = $countItems($beforeRows);
            $aCount = $countItems($afterRows);

            // perubahan bilangan row (mesra pengguna)
            if ($bCount !== $aCount) {
                $out[] = "{$label}: bilangan rekod {$bCount} → {$aCount}";
            }

            $max = max($bCount, $aCount);

            for ($i=0; $i<$max; $i++) {
                $bRow = is_array($beforeRows[$i] ?? null) ? $beforeRows[$i] : [];
                $aRow = is_array($afterRows[$i] ?? null) ? $afterRows[$i] : [];

                // row baru ditambah
                if (empty($bRow) && !empty(array_filter($aRow, fn($x) => $norm($x) !== ''))) {
                    $out[] = "Maklumat {$label} (rekod baharu) telah ditambah.";
                    continue;
                }

                // row dibuang
                if (empty($aRow) && !empty(array_filter($bRow, fn($x) => $norm($x) !== ''))) {
                    $out[] = "Maklumat {$label} (rekod {$ordinal}) telah dipadam.";
                    continue;
                }

                // perubahan field-level
                $changedFields = [];
                foreach ($fields as $f => $fLabel) {
                    $bv = $norm($bRow[$f] ?? '');
                    $av = $norm($aRow[$f] ?? '');

                    if ($bv !== $av) {
                        // ringkas (tidak papar nilai lama/baru)
                        $changedFields[] = $fLabel;

                        // Jika mahu papar nilai:
                        // $changedFields[] = "{$fLabel} ('{$bv}' → '{$av}')";
                    }
                }

                if (!empty($changedFields)) {
                    $ordinal = match ($i + 1) {
    1 => 'pertama',
    2 => 'kedua',
    3 => 'ketiga',
    4 => 'keempat',
    5 => 'kelima',
    default => 'ke-' . ($i + 1),
};

$out[] = "Maklumat {$label} (rekod {$ordinal}) telah dikemaskini.";

                }
            }
        }

        if (empty($out)) {
            $out[] = "Perubahan kecil / format (tiada beza ketara).";
        }

        // limit supaya tak terlalu panjang
        $maxLines = 8;
        if (count($out) > $maxLines) {
            $more = count($out) - $maxLines;
            $out = array_slice($out, 0, $maxLines);
            $out[] = "…dan {$more} perubahan lagi.";
        }

        return $out;
    };
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0 text-primary">
            Log Status / Timeline
        </h4>
    </div>

    <div class="card-body">
        @if($logs->isEmpty())
            <div class="text-muted">
                Tiada log.
            </div>
        @else
            <div class="position-relative">
                @foreach($logs as $i => $log)
                    @php
                        $roleKeyLocal = strtolower((string)($log->actor_role ?? ''));
                        $roleTxt = $roleLabel[$roleKeyLocal] ?? strtoupper($log->actor_role ?? '-');
                        $badgeCls = $roleBadgeClass[$roleKeyLocal] ?? 'bg-light text-dark border';

                        $meta = is_array($log->meta) ? $log->meta : [];

                        $from = $statusLabel[$log->from_status ?? ''] ?? ($log->from_status ?? '-');
                        $to   = $statusLabel[$log->to_status ?? ''] ?? ($log->to_status ?? '-');

                        $action = (string)($log->action ?? '');
                        $a = $actionText[$action] ?? null;

                        $title = \App\Helpers\PerformanceHelper::actionLabel($action);
$desc  = $a['desc'] ?? null;

                        $dateText = optional($log->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i');
                        $isLast = ($i === $logs->count() - 1);

                        // ✅ Actor detail (siapa yang buat)
                        $actorName  = $log->actor?->name ?? null;
                        $actorEmail = $log->actor?->email ?? null;

                        // ✅ Reason (untuk semua action yang simpan reason)
                        $reason = $meta['reason'] ?? null;

                        // ✅ Before/After snapshot (kalau ada)
                        $before = $meta['before'] ?? null;
                        $after  = $meta['after'] ?? null;

                        // ✅ IP optional
                        $ip = $meta['ip'] ?? null;

                        // ✅ Ringkasan perubahan (mesra pengguna)
                        $changeSummary = (!empty($before) || !empty($after)) ? $summarizeChanges($before, $after) : [];
                    @endphp

                    <div class="d-flex align-items-start gap-3 mb-4">

                        <div class="d-flex flex-column align-items-center" style="width:22px;">
                            <div class="rounded-circle border bg-white" style="width:12px;height:12px;margin-top:4px;"></div>
                            @if(!$isLast)
                                <div class="flex-grow-1" style="width:2px;background:#e5e7eb;margin-top:4px;min-height:40px;"></div>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge {{ $badgeCls }}">{{ $roleTxt }}</span>

                                <div class="fw-semibold">{{ $title }}</div>

                                <div class="ms-auto text-muted small">
                                    {{ $dateText }}
                                </div>
                            </div>

                            {{-- ✅ Papar siapa buat (nama admin/ppp/ppk/pyd) --}}
                            @if($actorName)
                                <div class="text-muted small mt-1">
                                    Oleh: <strong>{{ $actorName }}</strong>
                                    @if($actorEmail)
                                        <span class="text-muted">({{ $actorEmail }})</span>
                                    @endif
                                </div>
                            @endif

                            @if($desc)
                                <div class="text-muted small mt-1">
                                    {{ $desc }}
                                </div>
                            @endif

                            <div class="text-muted small mt-1">
                                Status: {{ $from }} → {{ $to }}
                            </div>

                            {{-- ✅ Papar sebab untuk semua action yang ada reason --}}
                            @if(!empty($reason))
                                <div class="text-muted small mt-2">
                                    <em>Sebab:</em> {{ $reason }}
                                </div>
                            @endif

                            {{-- ✅ Ringkasan perubahan (mesra pengguna, bukan JSON) --}}
                            @if(!empty($changeSummary))
                                <div class="mt-2">
                                    <div class="fw-semibold small">Ringkasan Perubahan:</div>
                                    <ul class="mb-0 small text-muted">
                                        @foreach($changeSummary as $line)
                                            <li>{{ $line }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- ✅ Papar perubahan PPSM (audit) --}}
                            @if(($log->action ?? '') === 'UPDATE_PPSM')
                                @php
                                    $fromP = isset($meta['ppsm_from']) && $meta['ppsm_from'] !== null
                                        ? number_format((float)$meta['ppsm_from'], 2)
                                        : '-';

                                    $toP = isset($meta['ppsm_to']) && $meta['ppsm_to'] !== null
                                        ? number_format((float)$meta['ppsm_to'], 2)
                                        : '-';
                                @endphp

                                <div class="text-muted small mt-2">
                                    <strong>PPSM:</strong>
                                    <span>{{ $fromP }}</span>
                                    <span class="mx-1">→</span>
                                    <span>{{ $toP }}</span>
                                </div>
                            @endif

                            @if(!empty($meta['completed_sections']))
                                <div class="text-success small mt-2">
                                    ✔ Bahagian lengkap: {{ implode(', ', (array) $meta['completed_sections']) }}
                                </div>
                            @endif

                            @if(!empty($meta['missing_sections']))
                                <div class="text-danger small">
                                    ✖ Bahagian belum lengkap: {{ implode(', ', (array) $meta['missing_sections']) }}
                                </div>
                            @endif

                            @if(isset($meta['ppp_total_score']))
                                <div class="text-muted small mt-1">
                                    Markah PPP: {{ $meta['ppp_total_score'] }}
                                </div>
                            @endif

                            {{-- ✅ Butiran teknikal JSON (hanya untuk Admin + debug=1) --}}
                            @if(($roleKeyLocal === 'admin') && $debug && (!empty($before) || !empty($after)))
                                <div class="mt-2">
                                    <details>
                                        <summary class="text-primary small" style="cursor:pointer;">
                                            Butiran teknikal (Before → After)
                                        </summary>

                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="fw-semibold small mb-1">Sebelum</div>
                                                <pre class="bg-light border rounded p-2 small" style="white-space:pre-wrap;">{{ json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="fw-semibold small mb-1">Selepas</div>
                                                <pre class="bg-light border rounded p-2 small" style="white-space:pre-wrap;">{{ json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
