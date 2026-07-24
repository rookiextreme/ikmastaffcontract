@extends('layouts.backend.master')

@section('title', 'Borang Penilaian (PPP)')

@section('content')

@php
    $roleKey  = 'ppp';
    $status   = $evaluation->status ?? 'DRAFT';

    // base url untuk tab nav
    $baseUrl  = route('ppp.performance.show', $evaluation->id);

    // default PPP = III
    $bahagian = strtoupper(trim(request('bahagian', 'III')));

    // ✅ PPP hanya boleh edit bila status SUBMITTED (selepas hantar -> PPP_SCORED lock)
    $is_locked = ($status !== 'SUBMITTED');

    // ✅ PPP boleh simpan hanya pada III/IV/V/VI/VIII
    $canSaveThisPage = in_array($bahagian, ['III','IV','V','VI','VIII'], true);
@endphp

<div class="card mb-6">
    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1">Borang Penilaian (PPP)</h3>
                <div class="text-muted">
                    PYD: <strong>{{ $evaluation->assignment->pydUser->name ?? '-' }}</strong><br>
                    Status:
<strong>
    {{ \App\Helpers\PerformanceHelper::statusLabel($status) }}
</strong><br>
                    Tahun: <strong>{{ $evaluation->period->year ?? '-' }}</strong>
                </div>
            </div>
            <a href="{{ route('ppp.performance.index') }}" class="btn btn-light">Kembali</a>
        </div>

        {{-- ✅ ERROR VALIDATION (min 3 aksara, required, dll) --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Ralat:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ✅ TAB NAV --}}
        @include('performance.partials.sections-nav', compact('bahagian','baseUrl','required','completedMap'))

        {{-- ===========================
            FORM SIMPAN (PPP)
        ============================ --}}
        <form method="POST" action="{{ route('ppp.performance.save', $evaluation->id) }}">
            @csrf

            {{-- ✅ penting supaya controller tahu tab mana sedang disimpan --}}
            <input type="hidden" name="bahagian" value="{{ $bahagian }}">

            {{-- ✅ render bahagian --}}
            @include('performance.partials.sections-render', [
                'evaluation'  => $evaluation,
                'items'       => $items ?? collect(),
                'scores'      => $scores ?? collect(),
                'roleKey'     => $roleKey,
                'bahagian'    => $bahagian,
                'sectionMeta' => $sectionMeta ?? [],
            ])

            <div class="d-flex justify-content-between mt-6">
                <button type="submit"
                        class="btn btn-light-primary"
                        {{ ($is_locked || !$canSaveThisPage) ? 'disabled' : '' }}>
                    Simpan
                </button>

                <div></div>
            </div>

            {{-- ✅ NOTIS LOCK --}}
            @if($is_locked && in_array($bahagian, ['III','IV','V','VI','VIII'], true))
                <div class="alert alert-warning mt-4 mb-0">
                    Borang telah dikunci. Status semasa: <strong>{{ $status }}</strong>
                </div>
            @endif
        </form>

        {{-- ✅ PERINGATAN PPP (muncul bila semua lengkap & sebelum hantar ke PPK) --}}
@if(!$is_locked && !empty($canSubmit))
    <div class="alert alert-warning mt-4">
        <div class="fw-semibold mb-1">
            ⚠️ Peringatan (Wajib)
        </div>
        <div>
            PPP perlu memaklumkan markah kepada PYD sebelum menghantar penilaian kepada PPK.
        </div>
        <div class="text-muted small mt-2">
            Sila pastikan PYD telah dimaklumkan sebelum anda menekan butang <strong>Hantar kepada PPK</strong>.
        </div>
    </div>
@endif


        {{-- ===========================
            FORM HANTAR (PPP → PPK)
            ✅ mesti luar form simpan (elak nested)
        ============================ --}}
        @if(!$is_locked)
            @if(!empty($canSubmit))
                <form method="POST"
                      action="{{ route('ppp.performance.submit', $evaluation->id) }}"
                      class="mt-4"
                      onsubmit="return confirm('Hantar kepada PPK? Selepas hantar, borang akan dikunci.');">
                    @csrf
                    <button class="btn btn-success">Hantar kepada PPK</button>
                </form>
            @else
                @if(!empty($missingSecs))
                    <div class="alert alert-warning mt-4 mb-0">
                        Sila lengkapkan bahagian wajib dahulu:
                        <strong>{{ implode(', ', $missingSecs) }}</strong>
                    </div>
                @endif
            @endif
        @endif

    </div>
</div>

@endsection
