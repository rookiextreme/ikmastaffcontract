@extends('layouts.backend.master')

@section('title', 'SKT (PPP)')

@section('content')
@php
    $roleKey = 'ppp';
    $status  = $evaluation->status ?? 'DRAFT';

    // ikut UI PYD: ?bahagian=I/II/III
    $bahagian = strtoupper(trim(request('bahagian', 'I')));

    // PPP edit dibenarkan hanya bila SUBMITTED/PPP_SCORED (selaras controller)
    $is_locked = !in_array($status, ['SUBMITTED','PPP_SCORED'], true);

    // baseUrl untuk tabs (PPP)
    $baseUrl = route('ppp.performance.skt.show', $evaluation->id);

    $period = $evaluation->period ?? null;

    $pydName = $evaluation->assignment?->pydUser?->name ?? '-';
    $pppName = $evaluation->assignment?->pppUser?->name ?? '-';

    $badgeClass = match($status){
        'SUBMITTED'   => 'bg-warning',
        'PPP_SCORED'  => 'bg-success',
        'PPK_APPROVED'=> 'bg-primary',
        default       => 'bg-light text-dark border',
    };
@endphp

<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">Sasaran Kerja Tahunan (SKT)</h3>
            <div class="text-muted">
                Lampiran "A" — PPP semak & sahkan.
            </div>

            <div class="text-muted mt-2">
                Tempoh:
                <strong>{{ $period->year ?? '-' }}</strong>
                @if(isset($period->session) && $period->session)
                    / <strong>{{ $period->session }}</strong>
                @endif

                <span class="ms-3">PYD: <strong>{{ $pydName }}</strong></span>
                <span class="ms-3">PPP: <strong>{{ $pppName }}</strong></span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $badgeClass }}">
    Status: {{ \App\Helpers\PerformanceHelper::statusLabel($status) }}
</span>
            <a href="{{ route('ppp.performance.skt.index') }}" class="btn btn-light">Kembali</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    {{-- ✅ REUSE tabs + unlock logic dari PYD --}}
    @include('staff.performance.skt.partials.sections-nav', [
        'bahagian'   => $bahagian,
        'baseUrl'    => $baseUrl,
        'roleKey'    => $roleKey,
        'status'     => $status,
        'period'     => $period,
        'evaluation' => $evaluation,
    ])

    {{-- ✅ REUSE render sections PYD --}}
    @include('staff.performance.skt.partials.sections-render', [
        'bahagian'   => $bahagian,
        'roleKey'    => $roleKey,
        'status'     => $status,
        'is_locked'  => $is_locked,
        'evaluation' => $evaluation,
    ])

</div>
@endsection