@extends('layouts.backend.master')

@section('title', 'Senarai SKT (Admin)')

@section('content')
<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">Senarai SKT (Admin)</h3>
                <div class="text-muted">Paparan semua SKT untuk semakan Admin (read-only).</div>
            </div>
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
                Tiada Tempoh SKT aktif.
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

            {{-- Optional filter ringkas --}}
            <form class="row g-2 align-items-end mb-4" method="GET" action="{{ route('admin.performance.skt.index') }}">
                <div class="col-md-3">
                    <label class="form-label">Status (optional)</label>
                    <select name="status" class="form-select">
                        <option value="">-- semua --</option>
                        @foreach(['DRAFT','SUBMITTED','PPP_SCORED','PPP_REVIEWED'] as $st)
                            <option value="{{ $st }}" {{ ($status ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Tapis</button>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-light w-100" href="{{ route('admin.performance.skt.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:60px">#</th>
                        <th>PYD</th>
                        <th>PPP</th>
                        <th style="width:180px" class="text-center">Status</th>
                        <th style="width:160px" class="text-center">Tindakan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($rows as $i => $row)
                        @php
                            $pydName = $row->assignment?->pydUser?->name ?? '-';
                            $pppName = $row->assignment?->pppUser?->name ?? '-';
                            $st      = $row->status ?? '-';
                            $badge = match($st){
                                'SUBMITTED'   => 'bg-warning',
                                'PPP_SCORED'  => 'bg-success',
                                'PPP_REVIEWED'=> 'bg-success',
                                'PPK_APPROVED'=> 'bg-primary',
                                default       => 'bg-light text-dark border',
                            };
                        @endphp
                        <tr>
                            <td class="text-center">{{ $i+1 }}</td>
                            <td>{{ $pydName }}</td>
                            <td>{{ $pppName }}</td>
                            <td class="text-center"><span class="badge {{ $badge }}">Status: {{ $st }}</span></td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-primary"
                                   href="{{ route('admin.performance.skt.show', $row->id) }}?bahagian=I">
                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Tiada rekod SKT.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endsection