@extends('layouts.backend.master')

@section('title','SKT (Admin)')

@section('content')

@php
    /**
     * ================================
     * FALLBACK & SAFETY
     * ================================
     */
    $bahagian = strtoupper((string)($bahagian ?? request('bahagian', 'I')));
    $baseUrl  = $baseUrl ?? url()->current();
    $status   = $status ?? ($evaluation->status ?? '-');

    // ADMIN = read-only mutlak
    $roleKey  = 'admin';
    $is_locked = true;

    $period = $evaluation->period ?? null;
    $assignment = $evaluation->assignment ?? null;

    $pydName = $assignment?->pydUser?->name ?? '-';
    $pppName = $assignment?->pppUser?->name ?? '-';
    $ppkName = $assignment?->ppkUser?->name ?? '-';

    /**
     * ==========================================
     * PREPROCESS DATA UNTUK ADMIN
     * ==========================================
     */

    // --- Bahagian I ---
    $sktI = (array)($evaluation->skt_bahagian_i ?? []);
    $itemsI = (array)($sktI['items'] ?? []);

    $itemsI = array_values(array_filter($itemsI, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return !($a === '' && $p === '');
    }));

    if (count($itemsI) < 1) {
        $itemsI = [['aktiviti' => '', 'petunjuk' => '']];
    }

    $sktI['items'] = $itemsI;
    $evaluation->skt_bahagian_i = $sktI;

    // --- Bahagian II ---
    $sktII = (array)($evaluation->skt_bahagian_ii ?? []);
    $tambah = (array)($sktII['tambah'] ?? []);
    $gugur  = (array)($sktII['gugur'] ?? []);

    $tambah = array_values(array_filter($tambah, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return !($a === '' && $p === '');
    }));

    $gugur = array_values(array_filter($gugur, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        return !($a === '');
    }));

    if (count($tambah) < 1) {
        $tambah = [['aktiviti' => '', 'petunjuk' => '']];
    }

    if (count($gugur) < 1) {
        $gugur = [['aktiviti' => '']];
    }

    $sktII['tambah'] = $tambah;
    $sktII['gugur']  = $gugur;
    $evaluation->skt_bahagian_ii = $sktII;
@endphp

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">Sasaran Kerja Tahunan (SKT)</h3>
            <div class="text-muted">
                Paparan SKT (Admin · Read-only)
            </div>

            <div class="text-muted mt-2">
                @if($period)
                    Tempoh:
                    <strong>{{ $period->year }}</strong>
                    @if(!empty($period->session))
                        / Sesi <strong>{{ $period->session }}</strong>
                    @endif
                    @if($period->is_active)
                        <span class="badge bg-light text-dark border ms-2">AKTIF</span>
                    @endif
                @endif
            </div>

            <div class="text-muted mt-1">
                PYD: <strong>{{ $pydName }}</strong>
                · PPP: <strong>{{ $pppName }}</strong>
                · PPK: <strong>{{ $ppkName }}</strong>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-light text-dark border">
                Status:
{{ \App\Helpers\PerformanceHelper::statusLabel($status) }}
            </span>

            {{-- ✅ Butang finalize hanya bila status PPP_REVIEWED --}}
            @if($status === 'PPP_REVIEWED')
                <button type="button"
                        class="btn btn-success"
                        data-bs-toggle="modal"
                        data-bs-target="#modalFinalizeSkt">
                    Muktamadkan SKT
                </button>
            @endif

            <a href="{{ route('admin.performance.evaluations.index', ['tab' => 'skt']) }}" class="btn btn-light">
                Kembali
            </a>
        </div>
    </div>

    {{-- ================= FLASH MESSAGE ================= --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info mb-4">
            {{ session('info') }}
        </div>
    @endif

    {{-- ✅ alert bila status sudah FINAL --}}
    @if($status === 'FINAL')
        <div class="alert alert-success mb-4">
            <strong>SKT telah dimuktamadkan.</strong><br>
            Rekod ini telah selesai di peringkat urus setia / admin dan status semasa ialah <strong>FINAL</strong>.
        </div>
    @endif

    {{-- ================= STATUS TIMELINE ================= --}}
    @includeIf('staff.performance.skt.partials.status-timeline', [
        'evaluation' => $evaluation
    ])

    {{-- ================= TAB BAHAGIAN ================= --}}
    @include('staff.performance.skt.partials.sections-nav', [
        'bahagian' => $bahagian,
        'baseUrl'  => $baseUrl,
        'roleKey'  => $roleKey,
        'status'   => $status,
    ])

    {{-- ================= RENDER SECTION ================= --}}
    @include('staff.performance.skt.partials.sections-render', [
        'bahagian'   => $bahagian,
        'evaluation' => $evaluation,
        'assignment' => $assignment,
        'period'     => $period,
        'roleKey'    => $roleKey,
        'is_locked'  => $is_locked,
    ])

</div>

{{-- ================= MODAL FINALIZE ================= --}}
@if($status === 'PPP_REVIEWED')
<div class="modal fade" id="modalFinalizeSkt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.performance.evaluations.finalize', $evaluation->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Muktamadkan SKT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-success">
                        Tindakan ini akan menukar status SKT daripada
                        <strong>PPP_REVIEWED</strong> kepada <strong>FINAL</strong>.

                        <div class="mt-2">
                            Gunakan tindakan ini apabila SKT telah selesai disemak oleh PPP dan
                            urus setia / admin ingin memuktamadkan rekod sebagai keputusan rasmi.
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

@endsection