@extends('layouts.backend.master')

@section('title', 'Penilaian SKT (PPP)')

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">Penilaian SKT (PPP)</h3>
            <div class="text-muted">
                @if($period)
                    Tempoh: <strong>{{ $period->year ?? '-' }}</strong>
                    <span class="mx-2">•</span>
                    Jenis: <strong>{{ $period->type ?? 'SKT' }}</strong>
                    <span class="badge ms-2 bg-light text-dark border">AKTIF</span>
                @else
                    <span class="text-danger">{{ $message ?? 'Tiada Tempoh SKT aktif.' }}</span>
                @endif
            </div>
        </div>

        <a href="{{ route('ppp.performance.index') }}" class="btn btn-light">
            Kembali Prestasi
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    @if(!$period)
        <div class="card">
            <div class="card-body text-muted">Tiada data untuk dipaparkan.</div>
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>PYD</th>
                                <th style="width:180px">Status</th>
                                <th style="width:160px">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $i => $row)
                                @php
                                    $pydName = $row->assignment?->pydUser?->name ?? '-';
                                    $status  = $row->status ?? '-';

                                    $badge = match($status){
                                        'SUBMITTED'   => 'bg-warning',
                                        'PPP_SCORED'  => 'bg-success',
                                        'PPK_APPROVED'=> 'bg-primary',
                                        default       => 'bg-light text-dark border',
                                    };
                                @endphp

                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>{{ $pydName }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badge }}">Status: {{ $status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-sm btn-primary"
                                           href="{{ route('ppp.performance.skt.show', $row->id) }}?bahagian=I">
                                            Buka
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Tiada SKT untuk PPP pada tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-muted small mt-3">
                    * PPP hanya boleh kemaskini Bahagian III apabila status <strong>SUBMITTED</strong> atau <strong>PPP_SCORED</strong>.
                </div>
            </div>
        </div>
    @endif

</div>
@endsection