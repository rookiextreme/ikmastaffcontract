@extends('layouts.backend.master')

@section('title', 'Pentadbiran | Lantikan (Prestasi)')

@section('content')

@php
    $tab  = $tab ?? request('tab', 'lnpt');
    $type = $type ?? ($tab === 'skt' ? 'SKT' : 'LNPT');

    $pageTitle = $type === 'SKT'
        ? 'Lantikan PPP (SKT)'
        : 'Lantikan PPP / PPK (Penilaian Prestasi)';

    $pageDesc = $type === 'SKT'
        ? 'Admin lantik PPP untuk PYD mengikut tempoh SKT (PPK tidak terlibat).'
        : 'Admin lantik PPP/PPK untuk PYD mengikut tempoh.';
@endphp

<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">{{ $pageTitle }}</h3>
                <div class="text-muted">{{ $pageDesc }}</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs nav-line-tabs mb-6">
            <li class="nav-item">
                <a class="nav-link {{ $tab==='skt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.assignments.index', ['tab'=>'skt']) }}">
                    SKT
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='lnpt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.assignments.index', ['tab'=>'lnpt']) }}">
                    Penilaian Prestasi
                </a>
            </li>
        </ul>

        {{-- Pilih period --}}
        <form method="GET" action="{{ route('admin.performance.assignments.index') }}" class="mb-6">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tempoh (Tahun)</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()">
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == ($period->id ?? null) ? 'selected' : '' }}>
                                {{ $p->year }}
                                @if(!is_null($p->session)) - Sesi {{ $p->session }} @endif
                                {{ $p->is_active ? '(AKTIF)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        {{-- Tambah lantikan --}}
        <div class="card border mb-8">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    Tambah Lantikan (Tempoh {{ $period->year ?? '-' }}
                    @if(!is_null($period->session ?? null)) - Sesi {{ $period->session }} @endif
                    )
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.performance.assignments.store', ['tab'=>$tab]) }}">
                    @csrf
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="performance_period_id" value="{{ $period->id }}">

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">PYD</label>
                            <select name="pyd_user_id" class="form-select" required>
                                <option value="">-- pilih PYD --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('pyd_user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pyd_user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">PPP</label>
                            <select name="ppp_user_id" class="form-select" {{ $type === 'SKT' ? 'required' : '' }}>
                                <option value="">-- pilih PPP --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ old('ppp_user_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ppp_user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        @if($type === 'LNPT')
                            <div class="col-md-4">
                                <label class="form-label">PPK</label>
                                <select name="ppk_user_id" class="form-select">
                                    <option value="">-- pilih PPK --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('ppk_user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ppk_user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        @else
                            {{-- SKT: PPK tidak terlibat --}}
                            <input type="hidden" name="ppk_user_id" value="">
                        @endif

                        <div class="col-12">
                            <button class="btn btn-primary">
                                {{ $type === 'SKT' ? 'Simpan Lantikan PPP (SKT)' : 'Simpan Lantikan' }}
                            </button>
                        </div>

                        @if($type === 'LNPT')
                            <div class="col-12">
                                <div class="text-muted small">
                                    Nota: PPP dan PPK tidak boleh sama.
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Senarai lantikan --}}
        <div class="card border">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    Senarai Lantikan ({{ $period->year ?? '-' }}
                    @if(!is_null($period->session ?? null)) - Sesi {{ $period->session }} @endif
                    )
                </h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                {{-- ✅ UBAH: # -> Bil --}}
                                <th style="width:60px;">Bil.</th>
                                <th>PYD</th>
                                <th>PPP</th>
                                @if($type === 'LNPT')
                                    <th>PPK</th>
                                @endif
                                <th style="width:300px;" class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- ✅ UBAH: buang $i => guna $loop->iteration --}}
                            @forelse($assignments as $a)
                                <tr>
                                    {{-- ✅ UBAH: $i+1 -> $loop->iteration --}}
                                    <td>{{ $loop->iteration }}</td>
                                    <td><strong>{{ $a->pydUser->name ?? '-' }}</strong></td>
                                    <td>{{ $a->pppUser->name ?? '-' }}</td>
                                    @if($type === 'LNPT')
                                        <td>{{ $a->ppkUser->name ?? '-' }}</td>
                                    @endif
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $a->id }}">
                                            Kemaskini
                                        </button>

                                        <form method="POST"
                                              action="{{ route('admin.performance.assignments.destroy', ['id'=>$a->id, 'tab'=>$tab, 'period_id'=>$period->id]) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light-danger" onclick="return confirm('Padam lantikan ini?')">
                                                Padam
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                {{-- Modal edit --}}
                                <div class="modal fade" id="modalEdit{{ $a->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kemaskini Lantikan: {{ $a->pydUser->name ?? 'PYD' }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <form method="POST" action="{{ route('admin.performance.assignments.update', ['id'=>$a->id, 'tab'=>$tab, 'period_id'=>$period->id]) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tab" value="{{ $tab }}">

                                                <div class="modal-body">
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <label class="form-label">PPP</label>
                                                            <select name="ppp_user_id" class="form-select" {{ $type === 'SKT' ? 'required' : '' }}>
                                                                <option value="">-- pilih PPP --</option>
                                                                @foreach($users as $u)
                                                                    <option value="{{ $u->id }}" {{ (int)$a->ppp_user_id === (int)$u->id ? 'selected' : '' }}>
                                                                        {{ $u->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        @if($type === 'LNPT')
                                                            <div class="col-md-6">
                                                                <label class="form-label">PPK</label>
                                                                <select name="ppk_user_id" class="form-select">
                                                                    <option value="">-- pilih PPK --</option>
                                                                    @foreach($users as $u)
                                                                        <option value="{{ $u->id }}" {{ (int)$a->ppk_user_id === (int)$u->id ? 'selected' : '' }}>
                                                                            {{ $u->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-12">
                                                                <div class="text-muted small">
                                                                    Nota: PPP dan PPK tidak boleh sama.
                                                                </div>
                                                            </div>
                                                        @else
                                                            {{-- SKT: paksa kosong --}}
                                                            <input type="hidden" name="ppk_user_id" value="">
                                                            <div class="col-12">
                                                                <div class="text-muted small">
                                                                    Nota: SKT hanya perlukan pengesahan oleh PPP.
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                                    <button class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="{{ $type === 'LNPT' ? 5 : 4 }}" class="text-center text-muted py-6">
                                        Tiada lantikan lagi untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection