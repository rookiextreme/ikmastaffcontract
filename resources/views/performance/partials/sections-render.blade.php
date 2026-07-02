@php
    $bahagian = $bahagian ?? request('bahagian', 'I');
    $roleKey  = $roleKey ?? 'pyd';
    $status   = $evaluation->status ?? 'DRAFT';

    $sectionMeta = $sectionMeta ?? [];
    $meta = $sectionMeta[$bahagian] ?? null;

    // lock status ikut flow awak
    $lockedPPP = !in_array($status, ['SUBMITTED','PPP_SCORED']);
    $lockedPPK = !in_array($status, ['PPP_SCORED','PPK_APPROVED']);
    $lockedPYD = !in_array($status, ['DRAFT']);

    $is_locked = match($roleKey) {
        'pyd' => $lockedPYD,
        'ppp' => $lockedPPP,
        'ppk' => $lockedPPK,
        default => true,
    };
@endphp

@if($meta)
    <div class="text-muted small mb-3">
        <strong>Bahagian {{ $bahagian }}: {{ $meta['title'] ?? '' }}</strong>
        @if(!empty($meta['weight']))
            — Wajaran: {{ $meta['weight'] }}%
        @endif
    </div>
@endif

{{-- =========================
    BAHAGIAN I
========================= --}}
@if($bahagian === 'I')
    <div class="card border mb-5">
        <div class="card-body">
            <h5 class="mb-4 text-primary">Bahagian I: Maklumat Penilaian</h5>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nama PYD</label>
                    <input class="form-control" value="{{ $evaluation->assignment->pydUser->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tempoh (Tahun)</label>
                    <input class="form-control" value="{{ $evaluation->period->year ?? '-' }}" disabled>
                </div>
                <div class="col-md-4">
    <label class="form-label">Jawatan</label>
    <input class="form-control"
           value="{{ $evaluation->assignment->pydUser->staffPosition->position->name ?? '-' }}"
           disabled>
</div>

<div class="col-md-4">
    <label class="form-label">Gred</label>
    <input class="form-control"
           value="{{ $evaluation->assignment->pydUser->staffPosition->grade->name ?? '-' }}"
           disabled>
</div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <input class="form-control" value="{{ $status }}" disabled>
                </div>

                <div class="col-md-6">
                    <label class="form-label">PPP</label>
                    <input class="form-control" value="{{ $evaluation->assignment->pppUser->name ?? '-' }}" disabled>
                </div>
                <div class="col-md-6">
                    <label class="form-label">PPK</label>
                    <input class="form-control" value="{{ $evaluation->assignment->ppkUser->name ?? '-' }}" disabled>
                </div>
            </div>

            <div class="text-muted small mt-2">
                * Bahagian ini paparan maklumat sahaja (read-only).
            </div>
        </div>
    </div>

{{-- =========================
    BAHAGIAN II (PYD edit) - ikut borang
========================= --}}
@elseif($bahagian === 'II')
    @include('performance.sections.II', [
        'evaluation' => $evaluation,
        'roleKey'    => $roleKey,
        'status'     => $status,
    ])

{{-- =========================
    BAHAGIAN III (UI BORANG BARU)
========================= --}}
@elseif($bahagian === 'III')
    <div class="card border mb-5">
        <div class="card-body">
            <h5 class="mb-3 text-primary">
                Bahagian {{ $bahagian }}: {{ $meta['title'] ?? 'Kompetensi' }}
                @if(!empty($meta['weight']))
                    <span class="text-muted">(Wajaran {{ $meta['weight'] }}%)</span>
                @endif
            </h5>

            <div class="text-muted small mb-3">
                Pegawai Penilai dikehendaki membuat penilaian berdasarkan pencapaian kerja sebenar PYD berbanding dengan SKT yang ditetapkan.
                Penilaian hendaklah berasaskan kepada penjelasan dan kriteria yang dinyatakan di bawah dengan menggunakan skala 1 hingga 10.
            </div>

            @if(in_array($roleKey, ['ppp','ppk']))
                @include('performance.partials.competency-table')
            @else
                @include('performance.partials.competency-table-readonly')
            @endif
        </div>
    </div>

{{-- =========================
    BAHAGIAN IV–VI (Kompetensi) - kekal dulu
========================= --}}
@elseif(in_array($bahagian, ['IV','V','VI']))
    <div class="card border mb-5">
        <div class="card-body">

            <h5 class="mb-3 text-primary">
                Bahagian {{ $bahagian }}: {{ $meta['title'] ?? 'Kompetensi' }}
                @if(!empty($meta['weight']))
                    <span class="text-muted">(Wajaran {{ $meta['weight'] }}%)</span>
                @endif
            </h5>

            {{-- ✅ INTRO TEXT: IV/V guna ayat standard, VI ikut borang (gambar) --}}
            @if($bahagian === 'VI')

                <div class="text-muted mb-2" style="font-size:0.9rem;">
                    (Sukan / Pertubuhan / Sumbangan Kreatif)
                </div>

                <div class="text-muted mb-3" style="font-size:0.95rem; line-height:1.5;">
                    Berasaskan maklumat di Bahagian II perenggan 1, Pegawai Penilai dikehendaki memberi penilaian
                    dengan menggunakan skel 1 hingga 10.
                    <span class="d-block">
                        Tiada sebarang markah boleh diberikan (kosong) jika PYD tidak mencatat kegiatan atau sumbangannya.
                    </span>
                </div>

            @else

                <div class="text-muted mb-3" style="font-size:0.95rem;">
                    Pegawai Penilai dikehendaki memberikan penilaian berasaskan kepada penjelasan setiap kriteria
                    yang dinyatakan di bawah dengan menggunakan skala 1 hingga 10:
                </div>

            @endif

            @if(in_array($roleKey, ['ppp','ppk']))
                @include('performance.partials.competency-table')
            @else
                @include('performance.partials.competency-table-readonly')
            @endif

        </div>
    </div>

{{-- =========================
    BAHAGIAN VII (Auto)
========================= --}}
@elseif($bahagian === 'VII')
@php
    $totalPPP = (float)($evaluation->ppp_total_score ?? 0);
    $totalPPK = (float)($evaluation->ppk_total_score ?? 0);

    // ✅ Markah Purata (Auto)
    $purata = ($totalPPP + $totalPPK) / 2;

    // ✅ Markah PPSM (Manual)
    $ppsm = $evaluation->ppsm_score ?? null;

    // ✅ Admin sahaja boleh edit
    $canEditPPSM = ($roleKey === 'admin');
    // Papar kepada pengguna hanya selepas admin muktamadkan
$isFinalized = strtoupper((string)($evaluation->status ?? '')) === 'FINAL';
@endphp

<div class="card border mb-5">
    <div class="card-body">
        <h5 class="mb-4 text-primary">Bahagian VII: Jumlah Markah Keseluruhan</h5>

        <div class="row g-3">

            {{-- Jumlah PPP --}}
            <div class="col-md-6">
                <div class="alert alert-info mb-0">
                    <div class="fw-semibold">Jumlah PPP</div>
                    <div class="fs-4">{{ number_format($totalPPP, 2) }}</div>
                </div>
            </div>

            {{-- Jumlah PPK --}}
            <div class="col-md-6">
                <div class="alert alert-info mb-0">
                    <div class="fw-semibold">Jumlah PPK</div>
                    <div class="fs-4">{{ number_format($totalPPK, 2) }}</div>
                </div>
            </div>

            {{-- ✅ Markah Purata (Auto) --}}
            <div class="col-md-6">
                <div class="alert alert-primary mb-0">
                    <div class="fw-semibold">Markah Purata (Auto)</div>
                    <div class="fs-4">{{ number_format($purata, 2) }}</div>
                    <div class="text-muted small mt-1">
                        (Jumlah PPP + Jumlah PPK) ÷ 2
                    </div>
                </div>
            </div>

         {{-- ✅ Markah PPSM (Manual) --}}
<div class="col-md-6">
    <div class="alert alert-warning mb-0">
        <div class="fw-semibold">Markah PPSM (Manual)</div>

        @if($canEditPPSM)
            <form method="POST" action="{{ route('admin.performance.evaluations.ppsm', $evaluation->id) }}">
                @csrf

                {{-- MARKAH PPSM --}}
                <input type="number"
                       name="ppsm_score"
                       step="0.01"
                       min="0"
                       max="100"
                       class="form-control mt-2"
                       value="{{ old('ppsm_score', $ppsm) }}"
                       placeholder="Contoh: 85.50">

                @error('ppsm_score')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror

                {{-- 🔒 SEBAB KEMASKINI PPSM (WAJIB UNTUK AUDIT) --}}
                <div class="mt-3">
                    <label class="form-label fw-semibold">
                        Sebab Kemaskini PPSM
                        <span class="text-danger">*</span>
                    </label>

                    <textarea name="reason"
                              rows="3"
                              class="form-control"
                              required>{{ old('reason') }}</textarea>

                    @error('reason')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary btn-sm mt-3">
                    Simpan Markah PPSM
                </button>
            </form>
        @else

    @if($isFinalized)

        <div class="fs-4 mt-1">
            {{ $ppsm !== null ? number_format((float)$ppsm, 2) : '-' }}
        </div>

        <div class="text-muted small mt-1">
            (Dikemaskini oleh admin/UPSM)
        </div>

    @else

        <div class="text-muted mt-2">
            Markah PPSM belum dimuktamadkan.
        </div>

    @endif

@endif
    </div>
</div>


        </div>

        <div class="text-muted small mt-3">
            Bahagian ini auto (paparan sahaja). Markah PPSM hanya akan dipaparkan kepada semua peranan selepas penilaian dimuktamadkan oleh Admin/UPSM.
        </div>
    </div>
</div>


{{-- =========================
    BAHAGIAN VIII (PPP)
    (Isi sama, tapi CSS ikut gaya standard sistem)
========================= --}}
@elseif($bahagian === 'VIII')
@php
    // PPP info (read-only)
    $pppUser = $evaluation->assignment->pppUser ?? null;

    // (tukar ikut struktur sebenar sistem awak)
    $pppJawatan     = $pppUser->jawatan ?? ($pppUser->position_name ?? '-');
    $pppKementerian = $pppUser->kementerian_jabatan ?? ($pppUser->department_name ?? 'INSTITUT KOPERASI MALAYSIA');

    $pppConfirmedAt = $evaluation->ppp_confirmed_at ?? null;

    $pppStatusText = match ($status) {
        'PPP_SCORED', 'PPK_APPROVED' => 'DISAHKAN OLEH PPP',
        'SUBMITTED' => 'MENUNGGU PENGESAHAN PPP',
        default => 'BELUM DISAHKAN',
    };

$editable = ($roleKey === 'ppp' && in_array($status, ['SUBMITTED'], true));
@endphp

<div class="card border mb-5">
    <div class="card-body">

        <h5 class="mb-4 text-primary">Bahagian VIII: Ulasan Keseluruhan PPP</h5>

        {{-- 1) Tempoh pengawasan --}}
        <div class="mb-4">
            <div class="fw-semibold mb-2">
                1) Tempoh PYD bertugas di bawah pengawasan
                <span class="text-danger">*</span>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" min="0" max="99"
                           name="ppp_supervise_years"
                           class="form-control"
                           value="{{ old('ppp_supervise_years', $evaluation->ppp_supervise_years) }}"
                           {{ $editable ? '' : 'readonly' }}>
                    <div class="form-text">&nbsp;</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="number" min="0" max="11"
                           name="ppp_supervise_months"
                           class="form-control"
                           value="{{ old('ppp_supervise_months', $evaluation->ppp_supervise_months) }}"
                           {{ $editable ? '' : 'readonly' }}>
                    <div class="form-text">0–11 bulan.</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small mt-4">
                        Isi tempoh berdasarkan tempoh PYD berada di bawah pengawasan PPP.
                    </div>
                </div>
            </div>
        </div>

        {{-- 2) Ulasan keseluruhan --}}
        <div class="mb-0">
            <div class="fw-semibold mb-2">
                2) Penilai Pertama hendaklah memberi ulasan keseluruhan prestasi PYD
                <span class="text-danger">*</span>
            </div>

            <div class="mb-3">
                <label class="form-label">(i) Prestasi keseluruhan</label>
                <textarea name="ppp_overall_performance"
                          rows="6"
                          class="form-control"
                          {{ $editable ? '' : 'readonly' }}
                >{{ old('ppp_overall_performance', $evaluation->ppp_overall_performance) }}</textarea>
            </div>

            <div>
                <label class="form-label">(ii) Kemajuan kerjaya</label>
                <textarea name="ppp_career_progress"
                          rows="6"
                          class="form-control"
                          {{ $editable ? '' : 'readonly' }}
                >{{ old('ppp_career_progress', $evaluation->ppp_career_progress) }}</textarea>
            </div>

            <div class="text-muted small mt-2">
                Minimum 3 aksara untuk setiap ulasan (cadangan).
            </div>
        </div>

        @if($roleKey === 'ppp' && $is_locked)
            <div class="alert alert-warning mt-4 mb-0">
                Bahagian VIII dikunci mengikut flow. Sila semak status penilaian.
            </div>
        @endif

    </div>
</div>

{{-- =========================
    BAHAGIAN IX (PPK) - ikut borang
========================= --}}
@elseif($bahagian === 'IX')
@php
    $editablePPK = ($roleKey === 'ppk' && !$is_locked);
@endphp

<div class="card border mb-5">
    <div class="card-body">
        <h5 class="mb-4 text-primary">Bahagian IX: Ulasan Keseluruhan oleh Pegawai Penilai Kedua</h5>

        {{-- 1) Tempoh pengawasan --}}
        <div class="mb-4">
            <div class="fw-semibold mb-2">
                1) Tempoh PYD bertugas di bawah pengawasan
                <span class="text-danger">*</span>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" min="0" max="99"
                           name="ppk_supervise_years"
                           class="form-control"
                           value="{{ old('ppk_supervise_years', $evaluation->ppk_supervise_years) }}"
                           {{ $editablePPK ? '' : 'disabled' }}>
                    <div class="form-text">&nbsp;</div> {{-- spacer supaya selari --}}
                </div>

                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="number" min="0" max="11"
                           name="ppk_supervise_months"
                           class="form-control"
                           value="{{ old('ppk_supervise_months', $evaluation->ppk_supervise_months) }}"
                           {{ $editablePPK ? '' : 'disabled' }}>
                    <div class="form-text">0–11 bulan.</div>
                </div>
            </div>
        </div>

        {{-- 2) Ulasan keseluruhan --}}
        <div class="mb-0">
            <div class="fw-semibold mb-2">
                2) PPK hendaklah memberi ulasan keseluruhan pencapaian prestasi PYD berdasarkan ulasan keseluruhan oleh PPP
                <span class="text-danger">*</span>
            </div>

            <label class="form-label fw-semibold">Ulasan PPK</label>
            <textarea name="ppk_comment"
                      rows="6"
                      class="form-control"
                      {{ $editablePPK ? '' : 'readonly' }}
            >{{ old('ppk_comment', $evaluation->ppk_comment) }}</textarea>

            <div class="text-muted small mt-1">Minimum 3 aksara untuk lengkap.</div>
        </div>

        @if($roleKey === 'ppk' && $is_locked)
            <div class="alert alert-warning mt-4 mb-0">
                Bahagian IX dikunci mengikut flow. Sila semak status penilaian.
            </div>
        @endif
    </div>
</div>
@endif

