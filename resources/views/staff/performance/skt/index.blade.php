@extends('layouts.backend.master')

@section('title','SKT (PYD)')

@section('content')

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">Sasaran Kerja Tahunan (SKT)</h3>
                <div class="text-muted">Lampiran ‘A’ — PYD isi dan hantar kepada PPP.</div>
            </div>
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

            @if(empty($assignment))
                <div class="alert alert-warning">
                    Tiada lantikan PPP/PPK untuk anda dalam tempoh SKT ini.
                </div>
            @else

                @php
                    $eval = $evaluation ?? null;
                    $status = $eval->status ?? 'BELUM_JANA';
                @endphp

                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                        <tr class="text-muted">
                            <th style="width:60px;">#</th>
                            <th>PYD</th>
                            <th>PPP</th>
                            <th>PPK</th>
                            <th style="width:160px;">Status</th>
                            <th style="width:160px;">Tarikh Hantar</th>
                            <th style="width:140px;" class="text-end">Tindakan</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>
                                <strong>{{ $assignment->pydUser->name ?? '-' }}</strong>
                                @if(!empty($assignment->pydUser->no_staff))
                                    <div class="text-muted small">{{ $assignment->pydUser->no_staff }}</div>
                                @endif
                            </td>
                            <td>{{ $assignment->pppUser->name ?? '-' }}</td>
                            <td>{{ $assignment->ppkUser->name ?? '-' }}</td>

                            <td>
                                <span class="badge badge-light">{{ $status }}</span>
                            </td>

                            <td class="text-muted">
                                @php
                                    $dt = $eval?->skt_submitted_at ?? null;
                                @endphp
                                {{ $dt ? \Carbon\Carbon::parse($dt)->format('d/m/Y') : '-' }}
                            </td>

                            <td class="text-end">
                                @if($eval)
                                    <a class="btn btn-sm btn-primary"
                                       href="{{ route('staff.performance.skt.show', $eval->id) }}">
                                        Buka SKT
                                    </a>
                                @else
                                    <span class="text-muted">Belum dijana oleh Admin</span>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            @endif
        @endif

    </div>
</div>

@endsection