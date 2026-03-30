@extends('layouts.backend.master')

@section('title', 'Detail Log Prestasi')

@section('content')
<div class="card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Detail Log Prestasi</h3>
                <div class="text-muted">
                    PYD:
                    <strong>{{ $evaluation->assignment->pydUser->name ?? '-' }}</strong><br>

                    PPP:
                    <strong>{{ $evaluation->assignment->pppUser->name ?? '-' }}</strong> |
                    PPK:
                    <strong>{{ $evaluation->assignment->ppkUser->name ?? '-' }}</strong><br>

                    Tahun:
                    <strong>{{ $evaluation->period->year ?? '-' }}</strong><br>

                    Status:
                    <strong>{{ $evaluation->status }}</strong>
                </div>
            </div>

            <a href="{{ route('admin.performance.logs.index') }}"
               class="btn btn-light">
                Kembali
            </a>
        </div>

        {{-- 🔥 TIMELINE LOG --}}
        @include('performance.partials.status-timeline', [
            'evaluation' => $evaluation
        ])

    </div>
</div>
@endsection
