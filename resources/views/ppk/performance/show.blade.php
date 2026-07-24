@extends('layouts.backend.master')

@section('title', 'Borang Penilaian (PPK)')

@section('content')
@php
    $roleKey = 'ppk';
    $status  = $evaluation->status ?? 'DRAFT';

    // default bahagian untuk PPK ialah III
    $bahagian = strtoupper(trim(request('bahagian', 'III')));

    $baseUrl = route('ppk.performance.show', $evaluation->id);
    $sectionMeta = $sectionMeta ?? [];
@endphp

<div class="card mb-6">
    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1">Borang Penilaian (PPK)</h3>
                <div class="text-muted">
                    PYD: <strong>{{ $evaluation->assignment->pydUser->name ?? '-' }}</strong><br>
                    Status:
<strong>
    {{ \App\Helpers\PerformanceHelper::statusLabel($status) }}
</strong>
                </div>
            </div>
            <a href="{{ route('ppk.performance.index') }}" class="btn btn-light">Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @include('performance.partials.sections-nav', compact('bahagian','baseUrl','required','completedMap'))

        @php
            // ✅ PPK boleh simpan hanya pada III/IV/V/VI/IX
            $canSaveThisPage = in_array($bahagian, ['III','IV','V','VI','IX'], true);

            // ✅ lock bila bukan status yang dibenarkan untuk PPK edit
            // PPK boleh edit hanya bila status = PPP_SCORED
            $is_locked = !in_array($status, ['PPP_SCORED'], true);
        @endphp

        {{-- ===========================
            FORM SIMPAN (PPK)
        ============================ --}}
        <form method="POST" action="{{ route('ppk.performance.save', $evaluation->id) }}">
            @csrf

            {{-- ✅ PENTING: hantar bahagian supaya controller save tahu nak proses apa --}}
            <input type="hidden" name="bahagian" value="{{ $bahagian }}">

            @include('performance.partials.sections-render', compact(
                'bahagian','roleKey','evaluation','items','scores','sectionMeta'
            ))

            <div class="d-flex justify-content-between mt-6">
                <button type="submit" class="btn btn-light-primary"
                    {{ ($is_locked || !$canSaveThisPage) ? 'disabled' : '' }}>
                    Simpan
                </button>

                {{-- ===========================
                    FORM SAHKAN (PPK)
                    (asing, bukan nested form)
                ============================ --}}
                @if(!$is_locked)
                    @if($canApprove ?? false)
                        <button type="button"
                                class="btn btn-primary"
                                onclick="document.getElementById('ppk-approve-form').submit();">
                            Sahkan (PPK)
                        </button>
                    @else
                        <div class="alert alert-warning mb-0">
                            Sila lengkapkan bahagian wajib dahulu:
                            <strong>{{ implode(', ', $missingSecs ?? []) }}</strong>
                        </div>
                    @endif
                @endif
            </div>
        </form>

        {{-- ✅ FORM SAHKAN (PPK) - luar dari form simpan --}}
        <form id="ppk-approve-form" method="POST" action="{{ route('ppk.performance.approve', $evaluation->id) }}" class="d-none">
            @csrf
        </form>

    </div>
</div>
@endsection
