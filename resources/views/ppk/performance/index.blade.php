@extends('layouts.backend.master')

@section('title','Penilaian Prestasi (PPK)')

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="mb-4">Penilaian Prestasi (PPK)</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-bordered align-middle">
                <thead>
                <tr class="text-muted">
                    <th style="width:60px;" class="text-center">Bil.</th>
                    <th>PYD</th>
                    <th>Status</th>
                    <th style="width:120px;" class="text-end">Tindakan</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $i => $r)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><strong>{{ $r->assignment->pydUser->name ?? '-' }}</strong></td>
                        <td><span class="badge badge-light">{{ $r->status }}</span></td>
                        <td class="text-end">

    {{-- Lihat SKT (Read Only) --}}
    <a class="btn btn-sm btn-light-info"
       href="{{ route('ppk.performance.skt', $r->id) }}">
        <i class="fas fa-eye"></i> Lihat SKT
    </a>

    {{-- Penilaian Prestasi --}}
    <a class="btn btn-sm btn-primary"
       href="{{ route('ppk.performance.show', $r->id) }}">
        Detail
    </a>

</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-5">Tiada rekod.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
