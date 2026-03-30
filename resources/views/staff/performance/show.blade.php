@extends('layouts.backend.master')

@section('title', 'Penilaian Prestasi (PYD)')

@section('content')
@php
    $roleKey = 'pyd';
    $status  = $evaluation->status ?? 'DRAFT';

    // default bahagian PYD I
    $bahagian = strtoupper(trim(request('bahagian', 'I')));

    $baseUrl = route('staff.performance.show', $evaluation->id);
    $sectionMeta = $sectionMeta ?? [];

    // lock PYD hanya boleh edit bila DRAFT
    $is_locked = !in_array($status, ['DRAFT'], true);

    // PYD hanya simpan di Bahagian II (ikut design awak)
    $canSaveThisPage = in_array($bahagian, ['II'], true);
@endphp

<div class="card mb-6">
    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1">Penilaian Prestasi (PYD)</h3>
                <div class="text-muted">
                    Tempoh: <strong>{{ $evaluation->period->year ?? '-' }}</strong><br>
                    Status: <strong>{{ $status }}</strong><br>
                    PPP: {{ $evaluation->assignment->pppUser->name ?? '-' }} |
                    PPK: {{ $evaluation->assignment->ppkUser->name ?? '-' }}
                </div>
            </div>
            <a href="{{ route('staff.performance.index') }}" class="btn btn-light">Kembali</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @include('performance.partials.sections-nav', compact('bahagian','baseUrl','required','completedMap'))

        {{-- =========================
            FORM SIMPAN (PYD)
        ========================= --}}
        <form method="POST" action="{{ route('staff.performance.save', $evaluation->id) }}">
            @csrf

            {{-- penting kalau controller nak tahu bahagian --}}
            <input type="hidden" name="bahagian" value="{{ $bahagian }}">

            @include('performance.partials.sections-render', compact(
                'bahagian','roleKey','evaluation','items','scores','sectionMeta'
            ))

            <div class="d-flex justify-content-between mt-6">
                <button type="submit" class="btn btn-light-primary"
                    {{ ($is_locked || !$canSaveThisPage) ? 'disabled' : '' }}>
                    Simpan
                </button>

                {{-- Ruang kanan kosong, submit form akan diletak di luar (elak nested form) --}}
                <div></div>
            </div>
        </form>

        {{-- =========================
            FORM HANTAR (PYD → PPP)  ✅ (DI LUAR FORM SIMPAN)
        ========================= --}}
        @if(!$is_locked)
            @if($canSubmit ?? false)
                <form method="POST" action="{{ route('staff.performance.submit', $evaluation->id) }}" class="mt-4">
                    @csrf
                    <button class="btn btn-primary"
                            onclick="return confirm('Hantar kepada PPP?')">
                        Hantar kepada PPP
                    </button>
                </form>
            @else
                <div class="alert alert-warning mt-4 mb-0">
                    Sila lengkapkan bahagian wajib dahulu:
                    <strong>{{ implode(', ', $missingSecs ?? []) }}</strong>
                </div>
            @endif
        @endif

    </div>
</div>
@endsection
