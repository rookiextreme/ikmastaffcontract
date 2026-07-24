@extends('layouts.backend.master')

@section('title','Sasaran Kerja Tahunan (SKT)')

@section('content')
@php
    // =========================
    // CONTEXT
    // =========================
    $roleKey  = $roleKey ?? 'pyd'; // pyd | ppp | admin
    $bahagian = strtoupper((string)($bahagian ?? request('bahagian','I')));
    $baseUrl  = $baseUrl ?? '#';

    $e = $evaluation ?? null;

    // =========================
    // STATUS SKT (derived + support FINAL)
    // =========================
    $statusSkt = 'BELUM_JANA';
    if ($e) {
        if (($e->status ?? null) === 'FINAL') {
            $statusSkt = 'FINAL';
        } elseif (($e->status ?? null) === 'RETURNED_BY_PPP') {
    $statusSkt = 'RETURNED_BY_PPP';
} elseif (($e->status ?? null) === 'PPP_REVIEWED') {
    $statusSkt = 'PPP_REVIEWED';
        } elseif (empty($e->skt_submitted_at)) {
            $statusSkt = 'DRAFT';
        } elseif (!empty($e->skt_submitted_at) && empty($e->skt_ppp_reviewed_at)) {
            $statusSkt = 'SUBMITTED';
        } else {
            $statusSkt = 'PPP_REVIEWED';
        }
    }

    // =========================
    // LOCKING LOGIC (ikut role)
    // =========================
    $lockedPYD = !in_array($statusSkt, ['DRAFT', 'RETURNED_BY_PPP'], true);
    $lockedPPP = !in_array($statusSkt, ['SUBMITTED','PPP_REVIEWED'], true);

    $is_locked = match($roleKey){
    'pyd'   => $lockedPYD,
    'ppp'   => $lockedPPP,
    'ppk'   => true,   // ✅ PPK hanya boleh lihat
    'admin' => true,
    default => true,
};

    // =========================
    // INFO PAPARAN
    // =========================
    $periodYear = $period->year ?? '-';
    $periodSess = $period->session ?? null;

    $pydName = $assignment->pydUser->name ?? '-';
    $pppName = $assignment->pppUser->name ?? '-';

    $badgeClass = match($statusSkt){
        'DRAFT'        => 'bg-light text-dark border',
        'SUBMITTED'    => 'bg-warning',
        'RETURNED_BY_PPP' => 'bg-danger',
        'PPP_REVIEWED' => 'bg-success',
        'FINAL'        => 'bg-primary',
        default        => 'bg-light text-dark border',
    };
@endphp

<div class="container-fluid">

    <div class="card mb-6">
        <div class="card-body">

            {{-- HEADER --}}
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <h3 class="mb-1">Paparan Sasaran Kerja Tahunan (SKT) - PPK</h3>
                    <div class="text-muted">
                        PYD:
                        <strong>{{ $pydName }}</strong><br>

                        PPP:
                        {{ $pppName }}<br>

                        Status:
                        <strong>
    {{ \App\Helpers\PerformanceHelper::statusLabel($statusSkt) }}
</strong><br>

                        Tempoh:
                        <strong>{{ $periodYear }}</strong>
                        @if($periodSess)
                            | Sesi: <strong>{{ $periodSess }}</strong>
                        @endif

                        @if($period->is_active ?? false)
                            | <strong>AKTIF</strong>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">

    {{-- PPK --}}
    @if($roleKey === 'ppk')
        <a href="{{ route('ppk.performance.index') }}"
           class="btn btn-light">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>

        <a href="{{ $continueUrl ?? route('ppk.performance.index') }}"
   class="btn btn-primary">
    <i class="fas fa-star"></i>
    Teruskan Penilaian
</a>
    @endif

    {{-- ADMIN: Muktamadkan SKT --}}
    @if($roleKey === 'admin' && $e && $statusSkt === 'PPP_REVIEWED')
        <button type="button"
                class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#modalFinalizeSkt">
            Muktamadkan SKT
        </button>
    @endif

    {{-- ADMIN: PDF SKT hanya selepas FINAL --}}
    @if($roleKey === 'admin' && $e && $statusSkt === 'FINAL')
        <a href="{{ route('admin.performance.skt.pdf', $e->id) }}"
           target="_blank"
           class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i>
            PDF SKT
        </a>
    @endif

    {{-- ADMIN: Kembali --}}
    @if($roleKey === 'admin')
        <a href="{{ route('admin.performance.evaluations.index', ['tab' => 'skt']) }}"
           class="btn btn-light">
            Kembali
        </a>
    @endif

    {{-- PPP: Pulangkan Kepada PYD --}}
    @if($roleKey === 'ppp' && $e && $statusSkt === 'SUBMITTED')
        <button type="button"
                class="btn btn-warning"
                data-bs-toggle="modal"
                data-bs-target="#modalReturnSkt">
            Pulangkan Kepada PYD
        </button>
    @endif

    {{-- PPP: Kembali --}}
    @if($roleKey === 'ppp')
        <a href="{{ route('ppp.performance.skt.index') }}"
           class="btn btn-light">
            Kembali
        </a>
    @endif

</div>
  </div>
            {{-- FLASH MESSAGE --}}
            @if(session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger mb-4">{{ session('error') }}</div>
            @endif

            @if(session('info'))
                <div class="alert alert-info mb-4">{{ session('info') }}</div>
            @endif

            @if(!empty($message))
                <div class="alert alert-warning mb-4">{{ $message }}</div>
            @endif

            {{-- ALERT RETURNED BY PPP --}}
@if($roleKey === 'pyd' && $e && $statusSkt === 'RETURNED_BY_PPP')
    <div class="alert alert-warning border border-warning mb-4">
        <strong>SKT Dipulangkan Oleh PPP</strong><br>

        PPP telah memulangkan SKT ini untuk pembetulan.
        Sila semak catatan di bawah, buat pembetulan dan hantar semula kepada PPP.

        @if(!empty($e->ppp_return_remark))
            <hr>
            <strong>Catatan PPP:</strong><br>
            <div style="white-space: pre-line;">{{ $e->ppp_return_remark }}</div>
        @endif
    </div>
@endif

            {{-- ALERT FINAL --}}
            @if($roleKey === 'admin' && $e && $statusSkt === 'FINAL')
                <div class="alert alert-success mb-4">
                    <strong>SKT telah dimuktamadkan.</strong><br>
                    Rekod ini telah selesai di peringkat urus setia / admin dan status semasa ialah <strong>FINAL</strong>.
                </div>
            @endif

            {{-- EMPTY STATES --}}
            @if(!$period)
                <div class="alert alert-warning">
                    Tiada Tempoh SKT aktif. Sila hubungi Admin.
                </div>
            @elseif(!$assignment)
                <div class="alert alert-warning">
                    Tiada lantikan PPP/PPK untuk anda dalam tempoh ini.
                </div>
            @elseif(!$e)
                <div class="alert alert-warning">
                    SKT belum dijana oleh Admin.
                </div>
            @else

                {{-- TABS --}}
                @include('performance.skt.partials.sections-nav', [
                    'bahagian'  => $bahagian,
                    'baseUrl'   => $baseUrl,
                    'roleKey'   => $roleKey,
                    'statusSkt' => $statusSkt,
                ])

                {{-- RENDER SECTIONS --}}
                @include('performance.skt.partials.sections-render', [
                    'bahagian'   => $bahagian,
                    'evaluation' => $e,
                    'assignment' => $assignment,
                    'period'     => $period,
                    'roleKey'    => $roleKey,
                    'statusSkt'  => $statusSkt,
                    'is_locked'  => $is_locked,
                ])

            @endif

        </div>
    </div>
</div>

{{-- MODAL FINALIZE ADMIN --}}
@if($roleKey === 'admin' && $e && $statusSkt === 'PPP_REVIEWED')
<div class="modal fade" id="modalFinalizeSkt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.performance.evaluations.finalize', $e->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Muktamadkan SKT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-success">
                        Tindakan ini akan menukar status SKT daripada
                        <strong>
    {{ \App\Helpers\PerformanceHelper::statusLabel('PPP_REVIEWED') }}
</strong>
kepada
<strong>
    {{ \App\Helpers\PerformanceHelper::statusLabel('FINAL') }}
</strong>.
                        <div class="mt-2">
                            Gunakan tindakan ini apabila SKT telah selesai disemak oleh PPP dan
                            admin ingin memuktamadkan rekod sebagai keputusan rasmi.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sebab Muktamad (Wajib)</label>
                        <textarea name="reason"
                                  rows="3"
                                  class="form-control"
                                  required
                                  minlength="5"
                                  maxlength="1000"
                                  placeholder="Contoh: SKT telah lengkap disemak oleh PPP dan dimuktamadkan oleh urus setia...">{{ old('reason') }}</textarea>
                        <div class="text-muted small mt-1">
                            Minimum 5 aksara. Maksimum 1000 aksara.
                        </div>
                    </div>

                    @error('reason')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="return confirm('Pasti mahu muktamadkan SKT ini? Selepas dimuktamadkan, status akan menjadi FINAL.')">
                        Ya, Muktamadkan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endif
{{-- MODAL RETURN SKT PPP --}}
@if($roleKey === 'ppp' && $e && $statusSkt === 'SUBMITTED')
<div class="modal fade" id="modalReturnSkt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('ppp.performance.skt.return', $e->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Pulangkan SKT Kepada PYD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        Tindakan ini akan memulangkan SKT kepada PYD untuk pembetulan.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sebab Pemulangan</label>
                        <textarea name="ppp_return_remark"
                                  rows="4"
                                  class="form-control"
                                  required
                                  minlength="5"
                                  maxlength="1000"
                                  placeholder="Contoh: Sila lengkapkan petunjuk prestasi Bahagian I.">{{ old('ppp_return_remark') }}</textarea>
                    </div>

                    @error('ppp_return_remark')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>

                    <button type="submit"
                            class="btn btn-warning"
                            onclick="return confirm('Pasti mahu pulangkan SKT ini kepada PYD?')">
                        Ya, Pulangkan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endif

@endsection