@extends('layouts.backend.master')

@section('title', 'Log Prestasi')

@section('content')
@php
    use App\Helpers\PerformanceHelper;
@endphp

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title mb-1">Log Prestasi (Admin)</h3>
            <div class="text-muted small">
                Senarai rekod aktiviti dan perubahan status SKT / LNPT.
            </div>
        </div>
    </div>

    <div class="card-body">

        {{-- FILTER --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input type="text"
                       name="year"
                       value="{{ $year }}"
                       class="form-control"
                       placeholder="cth: 2026">
            </div>

            <div class="col-md-3">
                <label class="form-label">Modul</label>
                <select name="module" class="form-select">
                    <option value="">Semua</option>
                    <option value="SKT" {{ ($module ?? '') === 'SKT' ? 'selected' : '' }}>SKT</option>
                    <option value="LNPT" {{ ($module ?? '') === 'LNPT' ? 'selected' : '' }}>LNPT</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach(['DRAFT','SUBMITTED','RETURNED_BY_PPP','PPP_REVIEWED','PPK_APPROVED','FINAL'] as $st)
                        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                            {{ PerformanceHelper::statusLabel($st) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary">Cari</button>
                <a href="{{ route('admin.performance.logs.index') }}"
                   class="btn btn-light ms-2">
                    Reset
                </a>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:70px">Bil.</th>
                        <th>PYD</th>
                        <th>PPP</th>
                        <th>PPK</th>
                        <th style="width:100px">Tahun</th>
                        <th style="width:100px">Modul</th>
                        <th style="width:220px">Status</th>
                        <th style="width:120px">Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $r)
                        @php
                            $latestLog = $r->logs->first();
                            $logModule = $latestLog->meta['module'] ?? null;

                            if (empty($logModule)) {
                                $logModule = !empty($r->skt_submitted_at) || !empty($r->skt_ppp_reviewed_at)
                                    ? 'SKT'
                                    : 'LNPT';
                            }
                        @endphp

                        <tr>
                            <td>{{ $rows->firstItem() + $loop->index }}</td>
                            <td>{{ $r->assignment->pydUser->name ?? '-' }}</td>
                            <td>{{ $r->assignment->pppUser->name ?? '-' }}</td>
                            <td>{{ $r->assignment->ppkUser->name ?? '-' }}</td>
                            <td>{{ $r->period->year ?? '-' }}</td>
                            <td>{!! PerformanceHelper::moduleBadge($logModule) !!}</td>
                            <td>{!! PerformanceHelper::statusBadge($r->status) !!}</td>
                            <td>
                                <a href="{{ route('admin.performance.logs.show', $r->id) }}"
                                   class="btn btn-sm btn-light-primary">
                                    Lihat Log
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Tiada rekod dijumpai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $rows->links() }}
        </div>

    </div>
</div>
@endsection