@extends('layouts.backend.master')

@section('title', 'SKT')

@section('content')
@php
    $roleKey = $roleKey ?? 'pyd'; // pyd/ppp
@endphp

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">
                    {{ $roleKey === 'ppp' ? 'Penilaian SKT (PPP)' : 'Sasaran Kerja Tahunan (SKT)' }}
                </h3>
                <div class="text-muted">
                    @if($roleKey === 'ppp')
                        Lampiran ‘A’ — PPP semak & sahkan.
                    @else
                        Lampiran ‘A’ — PYD isi dan hantar kepada PPP.
                    @endif
                </div>
            </div>

            @if($roleKey === 'ppp')
                <a href="{{ route('ppp.performance.index') }}" class="btn btn-light">Kembali Prestasi</a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-light">Kembali</a>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif
        @if(!empty($message))
            <div class="alert alert-warning mb-4">{{ $message }}</div>
        @endif

        @if(!$period)
            <div class="alert alert-warning">
                Tiada Tempoh SKT aktif. Sila hubungi Admin untuk aktifkan tempoh SKT.
            </div>
        @else
            <div class="mb-4 text-muted">
                Tempoh: <strong>{{ $period->year ?? '-' }}</strong>
                @if(!empty($period->session))
                    / Sesi <strong>{{ $period->session }}</strong>
                @endif
                @if(!empty($period->is_active))
                    <span class="badge bg-light text-dark border ms-2">AKTIF</span>
                @endif
            </div>

            {{-- PPP: list rows, PYD: direct link --}}
            @if($roleKey === 'ppp')
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
                        @forelse($rows ?? [] as $i => $row)
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
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ $pydName }}</td>
                                <td class="text-center">
    <span class="badge {{ $badge }}">
        Status: {{ \App\Helpers\PerformanceHelper::statusLabel($status) }}
    </span>
</td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-primary"
                                       href="{{ route('ppp.performance.skt.show', $row->id) }}?bahagian=I">
                                        Buka
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Tiada SKT untuk PPP pada tempoh ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-muted small mt-3">
                    * PPP hanya boleh kemaskini Bahagian III apabila status <strong>SUBMITTED</strong> atau <strong>PPP_SCORED</strong>.
                </div>
            @else
                <a class="btn btn-primary" href="{{ route('staff.performance.skt', ['bahagian' => 'I']) }}">
                    Buka Borang SKT
                </a>
            @endif
        @endif

    </div>
</div>
@endsection