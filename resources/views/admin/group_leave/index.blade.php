@extends('layouts.backend.master')
@section('title','Cuti Kelompok')

@section('content')
<div class="card mb-5 mb-xl-10">
    <div class="card-header">
        <h3 class="card-title">Senarai Permohonan Cuti Kelompok</h3>
        <div class="card-toolbar">
            <a href="{{ route('admin.group_leave.create') }}" class="btn btn-primary btn-sm">
                Permohonan Baharu
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>Staf</th>
                        <th>Tarikh Permohonan</th> {{-- ✅ baru --}}
                        <th>Tarikh</th>
                        <th>Bil. Hari</th>
                        <th>Status</th>
                        <th>Borang</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $row)
                        <tr>
                            <td class="text-start">
                                <b>{{ ucwords(strtolower($row->staff?->getUser?->name ?? '-')) }}</b><br>
                                <small class="text-muted">
                                    KP: {{ $row->staff?->getUser?->ic_no ?? '-' }} |
                                    Staf: {{ $row->staff?->getUser?->no_staff ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center">
    {{ optional($row->created_at)->format('d-m-Y') ?? '-' }}
</td>

                            <td>
                                {{ date('d-m-Y', strtotime($row->start_date)) }}
                                <br>hingga<br>
                                {{ date('d-m-Y', strtotime($row->end_date)) }}
                            </td>
                            <td>{{ $row->total_days }}</td>
                            <td>
                                @if($row->status === 'approved')
                                    <span class="badge badge-light-success">Approved</span>
                                @elseif($row->status === 'pending')
                                    <span class="badge badge-light-warning">Pending</span>
                                @else
                                    <span class="badge badge-light-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($row->attachment_path)
                                    <a target="_blank" href="{{ asset('storage/'.$row->attachment_path) }}" class="btn btn-sm btn-light-primary">
                                        Lihat
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $row->appliedBy?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">Tiada rekod.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
