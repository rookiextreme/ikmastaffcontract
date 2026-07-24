@extends('layouts.backend.master')

@section('title','SKT (PYD)')

@section('content')

@php
    $bahagian = strtoupper((string)($bahagian ?? request('bahagian','I')));
    $baseUrl  = $baseUrl ?? route('staff.performance.skt');

    // status SKT (guna tarikh skt_*)
    $eval = $evaluation ?? null;
    $statusSkt = 'BELUM_JANA';
    if($eval){
        if(empty($eval->skt_submitted_at)) $statusSkt = 'DRAFT';
        else if(!empty($eval->skt_submitted_at) && empty($eval->skt_ppp_reviewed_at)) $statusSkt = 'SUBMITTED';
        else $statusSkt = 'PPP_REVIEWED';
    }
@endphp

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">Sasaran Kerja Tahunan (SKT)</h3>
                <div class="text-muted">Lampiran ‘A’ — PYD isi dan hantar kepada PPP.</div>
            </div>

            @if($eval)
                <span class="badge bg-light text-dark border">
                    Status:
{{ \App\Helpers\PerformanceHelper::statusLabel($statusSkt) }}
                </span>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(!empty($message))
            <div class="alert alert-warning">{{ $message }}</div>
        @endif

        @if(!$period)
            <div class="alert alert-warning">
                Tiada Tempoh SKT aktif. Sila hubungi Admin untuk aktifkan tempoh SKT.
            </div>
        @elseif(empty($assignment))
            <div class="alert alert-warning">
                Tiada lantikan PPP/PPK untuk anda dalam tempoh SKT ini.
            </div>
        @elseif(!$eval)
            <div class="alert alert-warning">
                SKT belum dijana oleh Admin.
            </div>
        @else

            <div class="mb-4 text-muted">
                Tempoh: <strong>{{ $period->year }}</strong>
                @if(!empty($period->session))
                    / Sesi <strong>{{ $period->session }}</strong>
                @endif
                @if($period->is_active)
                    <span class="badge bg-light text-dark border ms-2">AKTIF</span>
                @endif
            </div>

            {{-- ✅ Tabs Bahagian --}}
            @include('staff.performance.skt.partials.sections-nav', [
                'bahagian' => $bahagian,
                'baseUrl'  => $baseUrl,
                'statusSkt'=> $statusSkt,
            ])

            {{-- ✅ Render Borang ikut Bahagian --}}
            @include('staff.performance.skt.partials.sections-render', [
                'bahagian'   => $bahagian,
                'evaluation' => $eval,
                'assignment' => $assignment,
                'period'     => $period,
            ])

        @endif

    </div>
</div>

@endsection