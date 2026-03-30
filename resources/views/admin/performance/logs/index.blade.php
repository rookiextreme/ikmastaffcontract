@extends('layouts.backend.master')

@section('title', 'Log Prestasi')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Log Prestasi (Admin)</h3>
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    @foreach(['DRAFT','SUBMITTED','PPP_SCORED','PPK_APPROVED'] as $st)
                        <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>
                            {{ $st }}
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
                        <th style="width:70px">ID</th>
                        <th>PYD</th>
                        <th>PPP</th>
                        <th>PPK</th>
                        <th style="width:120px">Tahun</th>
                        <th style="width:150px">Status</th>
                        <th style="width:120px"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r->id }}</td>
                            <td>{{ $r->assignment->pydUser->name ?? '-' }}</td>
                            <td>{{ $r->assignment->pppUser->name ?? '-' }}</td>
                            <td>{{ $r->assignment->ppkUser->name ?? '-' }}</td>
                            <td>{{ $r->period->year ?? '-' }}</td>
                            <td>
                                <span class="badge badge-light-primary">
                                    {{ $r->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.performance.logs.show', $r->id) }}"
                                   class="btn btn-sm btn-light-primary">
                                    Lihat Log
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
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
