@php
    $e = $evaluation;
    $roleKey = $roleKey ?? 'pyd';
    $status  = $status ?? ($e->status ?? 'DRAFT');
    $is_locked = $is_locked ?? false;

    $dataIII = (array)($e->skt_bahagian_iii ?? []);

    // lengkap I & II (untuk button submit PYD)
    $i = is_array($e->skt_bahagian_i ?? null) ? $e->skt_bahagian_i : [];
    $iItems = (array)($i['items'] ?? []);
    $filledI = 0; $okI = true;
    foreach($iItems as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        if($a === '' && $p === '') continue;
        $filledI++;
        if($a === '' || $p === ''){ $okI = false; break; }
    }
    $iComplete = ($filledI > 0) && $okI;

    $ii = is_array($e->skt_bahagian_ii ?? null) ? $e->skt_bahagian_ii : [];
    $tambah = (array)($ii['tambah'] ?? []);
    $gugur  = (array)($ii['gugur'] ?? []);

    $filledTambah = 0; $okTambah = true;
    foreach($tambah as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        if($a === '' && $p === '') continue;
        $filledTambah++;
        if($a === '' || $p === ''){ $okTambah = false; break; }
    }

    $filledGugur = 0;
    foreach($gugur as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        if($a === '') continue;
        $filledGugur++;
    }

    $iiComplete = (($filledTambah + $filledGugur) > 0) && $okTambah;

    $canSubmitPYD = ($status === 'DRAFT') && $iComplete && $iiComplete;

    // route ikut role
    $saveUrl = $roleKey === 'ppp'
        ? route('ppp.performance.skt.save', $e->id)
        : route('staff.performance.skt.save');

    $submitUrl = $roleKey === 'ppp'
        ? route('ppp.performance.skt.submit', $e->id)
        : route('staff.performance.skt.submit');

    $pppCanEdit = in_array($status, ['SUBMITTED','PPP_SCORED'], true) && !$is_locked;

    $ulasanPyd = trim((string)($dataIII['ulasan_pyd'] ?? ''));
    $ulasanPpp = trim((string)($dataIII['ulasan_ppp'] ?? ''));
@endphp

<style>
    .skt-readonly-box {
        min-height: 150px;
        background: #f8f9fb;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 14px 16px;
        color: #3f4254;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    .skt-section-note {
        font-size: 0.85rem;
        color: #7e8299;
        margin-top: 6px;
    }

    .skt-section-block + .skt-section-block {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #eff2f5;
    }
</style>

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0">
            BAHAGIAN III - Laporan dan Ulasan Keseluruhan Pencapaian Sasaran Kerja Tahunan Pada Akhir Tahun Oleh PYD dan PPP
        </h4>
    </div>

    <div class="card-body">

        {{-- =========================
            1) ULASAN PYD
        ========================== --}}
        <div class="mb-4 skt-section-block">
            <label class="form-label fw-semibold">1. Laporan / Ulasan Oleh PYD</label>

            @if($roleKey === 'admin' || $roleKey === 'ppp')
                <div class="skt-readonly-box">{!! nl2br(e($ulasanPyd !== '' ? $ulasanPyd : '-')) !!}</div>
                <div class="skt-section-note">
                    * Ruangan ini diisi oleh PYD.
                </div>
            @else
                <form method="POST" action="{{ $saveUrl }}">
                    @csrf
                    <input type="hidden" name="bahagian" value="III">

                    <textarea class="form-control"
                              rows="5"
                              name="skt_bahagian_iii[ulasan_pyd]"
                              {{ $is_locked ? 'readonly' : '' }}>{{ $dataIII['ulasan_pyd'] ?? '' }}</textarea>

                    <div class="mt-3">
                        <button type="submit"
                                class="btn btn-primary"
                                {{ $is_locked ? 'disabled' : '' }}>
                            Simpan
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- =========================
            2) ULASAN PPP
        ========================== --}}
        <div class="mb-4 skt-section-block">
            <label class="form-label fw-semibold">2. Laporan / Ulasan oleh PPP</label>

            @if($roleKey === 'admin')
                <div class="skt-readonly-box">{!! nl2br(e($ulasanPpp !== '' ? $ulasanPpp : '-')) !!}</div>
                <div class="skt-section-note">
                    * Ruangan ini diisi oleh PPP.
                </div>

            @elseif($roleKey === 'ppp')
                <form method="POST" action="{{ $saveUrl }}">
                    @csrf
                    <input type="hidden" name="bahagian" value="III">

                    <textarea class="form-control"
                              rows="5"
                              name="skt_bahagian_iii[ulasan_ppp]"
                              {{ $pppCanEdit ? '' : 'readonly' }}
                              placeholder="Ruangan ini akan diisi oleh PPP.">{{ $dataIII['ulasan_ppp'] ?? '' }}</textarea>

                    <div class="text-muted small mt-1">
                        * PPP boleh kemaskini semasa status <strong>SUBMITTED</strong> atau <strong>PPP_SCORED</strong>.
                    </div>

                    @if($pppCanEdit)
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan (PPP)</button>
                        </div>
                    @else
                        <div class="text-muted small mt-2">
                            * SKT telah disahkan / dikunci. Status semasa: <strong>{{ $status }}</strong>.
                        </div>
                    @endif
                </form>

                @if($pppCanEdit)
                    <form method="POST" action="{{ $submitUrl }}" class="mt-3">
                        @csrf
                        <button type="submit"
                                class="btn btn-success"
                                onclick="return confirm('Sahkan SKT sebagai PPP?')">
                            Sahkan / Hantar
                        </button>
                    </form>
                @endif

            @else
                <textarea class="form-control" rows="5" disabled>{{ $dataIII['ulasan_ppp'] ?? '' }}</textarea>
                <div class="text-muted small mt-1">
                    * Ruangan ini akan diisi oleh PPP.
                </div>
            @endif
        </div>

        {{-- =========================
            SUBMIT PYD
        ========================== --}}
        @if($roleKey === 'pyd')
            <div class="mt-4">
                @if($canSubmitPYD)
                    <form method="POST" action="{{ $submitUrl }}">
                        @csrf
                        <button type="submit"
                                class="btn btn-light-primary"
                                onclick="return confirm('Hantar SKT kepada PPP?')">
                            Hantar kepada PPP
                        </button>
                    </form>
                @else
                    @if($status === 'DRAFT')
                        <div class="alert alert-warning mb-0">
                            Lengkapkan <strong>Bahagian I</strong> dan <strong>Bahagian II</strong> dahulu sebelum boleh hantar kepada PPP.
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            SKT telah dihantar kepada PPP. Status semasa: <strong>{{ $status }}</strong>.
                        </div>
                    @endif
                @endif
            </div>
        @endif

    </div>
</div>