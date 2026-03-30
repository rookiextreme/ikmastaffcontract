@extends('layouts.backend.master')

@section('title', 'Penilaian Prestasi (PYD)')

@section('content')
@php
    // pastikan wujud walaupun controller pass null
    $evaluation  = $evaluation ?? null;

    $roleKey = 'pyd';

    // ✅ PENTING: guna nullsafe supaya tak crash bila $evaluation null
    $status  = $evaluation?->status ?? 'DRAFT';

    $bahagian = strtoupper(trim(request('bahagian', 'I')));

    // baseUrl tab nav ikut controller (index)
    $baseUrl = route('staff.performance.index');

    $sectionMeta = $sectionMeta ?? [];

    // PYD lock bila bukan DRAFT
    $is_locked = ($status !== 'DRAFT');

    // PYD hanya edit Bahagian II (ikut design awak)
    $canSaveThisPage = in_array($bahagian, ['II'], true);

    // fallback message jika controller tak hantar $message
    $message = $message ?? null;

    // optional: elak undefined variable warnings untuk nav
    $required     = $required     ?? [];
    $completedMap = $completedMap ?? [];
@endphp

<div class="card mb-6">
    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1">Penilaian Prestasi (PYD)</h3>

                {{-- ✅ Bila tiada evaluation, jangan sentuh relation --}}
                <div class="text-muted">
                    Tempoh:
                    <strong>{{ $evaluation?->period?->year ?? '-' }}</strong><br>

                    Status:
                    <strong>{{ $evaluation ? $status : '-' }}</strong><br>

                    PPP: {{ $evaluation?->assignment?->pppUser?->name ?? '-' }} |
                    PPK: {{ $evaluation?->assignment?->ppkUser?->name ?? '-' }}
                </div>
            </div>
        </div>

        {{-- alert mesej dari controller --}}
        @if(!empty($message))
            <div class="alert alert-warning">{{ $message }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- ✅ MODE: TIADA LANTIKAN / TIADA TASK --}}
        @if(!$evaluation)
            <div class="alert alert-warning mb-3">
                Tiada lantikan Penilaian Prestasi untuk anda.
            </div>

            <div class="alert alert-warning mb-0">
                Tiada lantikan PPP/PPK untuk anda dalam tempoh ini.
            </div>

        @else
            {{-- ✅ Bila ada evaluation barulah render tabs + form --}}
            @include('performance.partials.sections-nav', compact('bahagian','baseUrl','required','completedMap'))

            {{-- ✅ SATU FORM SAHAJA: SAVE + SUBMIT --}}
            <form method="POST" action="{{ route('staff.performance.save') }}">
                @csrf

                <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">
                <input type="hidden" name="bahagian" value="{{ $bahagian }}">

                @include('performance.partials.sections-render', compact(
                    'bahagian','roleKey','evaluation','items','scores','sectionMeta'
                ))

                <div class="d-flex justify-content-between mt-6">

                    {{-- ✅ SAVE --}}
                    <button type="submit"
                            name="action" value="save"
                            class="btn btn-light-primary"
                            {{ ($is_locked || !$canSaveThisPage) ? 'disabled' : '' }}>
                        Simpan
                    </button>

                    {{-- ✅ SUBMIT PYD → PPP --}}
                    @if(!$is_locked)
                        @if($canSubmit ?? false)
                            <button type="submit"
                                    name="action" value="submit"
                                    class="btn btn-primary"
                                    onclick="return confirm('Hantar kepada PPP?')">
                                Hantar kepada PPP
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
        @endif

    </div>
</div>
@endsection