@extends('layouts.backend.master')

@section('title', 'Pentadbiran | Tempoh Penilaian')

@section('content')

@php
    $tab  = $tab  ?? request('tab','lnpt');
    $type = $type ?? ($tab === 'skt' ? 'SKT' : 'LNPT');

    $titleForm = $type === 'SKT'
        ? 'Tambah Tempoh SKT'
        : 'Tambah Tempoh Penilaian Prestasi';

    $titleList = $type === 'SKT'
        ? 'Senarai Tempoh SKT'
        : 'Senarai Tempoh Penilaian Prestasi';
@endphp

<div class="card">
    <div class="card-body">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">Tempoh Penilaian</h3>
                <div class="text-muted">Setup tempoh sahaja (bukan isi borang).</div>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs nav-line-tabs mb-6">
            <li class="nav-item">
                <a class="nav-link {{ $tab==='skt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.periods.index', ['tab'=>'skt']) }}">
                    SKT
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='lnpt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.periods.index', ['tab'=>'lnpt']) }}">
                    Penilaian Prestasi
                </a>
            </li>
        </ul>

        {{-- ============ FORM TAMBAH TEMPOH ============ --}}
        <div class="card border mb-8">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ $titleForm }}</h4>
            </div>
            <div class="card-body">

                <form method="POST" action="{{ route('admin.performance.periods.store', ['tab'=>$tab]) }}">
                    @csrf
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="row g-4">
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year') }}" placeholder="2026" required>
                            @error('year') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        {{-- Session (optional) --}}
                        <div class="col-md-3">
                            <label class="form-label">Sesi (Pilihan)</label>
                            <select name="session" class="form-select">
                                <option value="">-</option>
                                <option value="1" {{ old('session')=='1' ? 'selected' : '' }}>1</option>
                                <option value="2" {{ old('session')=='2' ? 'selected' : '' }}>2</option>
                            </select>
                            @error('session') <div class="text-danger small">{{ $message }}</div> @enderror
                            <div class="text-muted small mt-1">Guna bila 2 kali setahun (contoh Sesi 1/2).</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tarikh Mula</label>
                            <input type="text" name="start_date" class="form-control date-ikma" value="{{ old('start_date') }}"required>
                            @error('start_date') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tarikh Tamat</label>
                            <input type="text" name="end_date" class="form-control date-ikma" value="{{ old('end_date') }}"required>
                            @error('end_date') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tempoh Aktif</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_create">
                                <label class="form-check-label" for="is_active_create">Set sebagai tempoh aktif</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nota (Pilihan)</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Pilihan">{{ old('note') }}</textarea>
                            @error('note') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary">
                                Simpan Tempoh
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============ SENARAI TEMPOH ============ --}}
        <div class="card border">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ $titleList }}</h4>
            </div>

            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th style="width: 90px;">Status</th>
                                <th style="width: 110px;">Tahun</th>
                                <th style="width: 80px;">Sesi</th>
                                <th>Tarikh</th>
                                <th>Nota</th>
                                <th style="width: 260px;" class="text-end">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($periods as $p)
                                <tr>
                                    <td>
                                        @if($p->is_active)
                                            <span class="badge badge-success">AKTIF</span>
                                        @else
                                            <span class="badge badge-light">TIDAK AKTIF</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $p->year }}</strong></td>
                                    <td>{{ $p->session ?? '-' }}</td>
                                    <td>
                                        <div class="text-muted">
    {{ $p->start_date ? \Carbon\Carbon::parse($p->start_date)->format('d/m/Y') : '-' }}
    &ndash;
    {{ $p->end_date ? \Carbon\Carbon::parse($p->end_date)->format('d/m/Y') : '-' }}
</div>
                                    </td>
                                    <td>{{ $p->note ?? '-' }}</td>

                                    <td class="text-end">
                                        {{-- Aktifkan --}}
                                        @if(!$p->is_active)
                                            <form method="POST"
                                                  action="{{ route('admin.performance.periods.activate', ['id'=>$p->id, 'tab'=>$tab]) }}"
                                                  class="d-inline">
                                                @csrf
                                                <input type="hidden" name="tab" value="{{ $tab }}">
                                                <button class="btn btn-sm btn-light-primary"
                                                        onclick="return confirm('Aktifkan tempoh {{ $p->year }}? Tempoh lain ({{ $type }}) akan dinyahaktifkan.')">
                                                    Jadikan Aktif
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Edit (inline modal) --}}
                                        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $p->id }}">
                                            Kemaskini
                                        </button>
                                    </td>
                                </tr>

                                {{-- MODAL EDIT --}}
                                <div class="modal fade" id="modalEdit{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Kemaskini Tempoh {{ $p->year }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <form method="POST" action="{{ route('admin.performance.periods.update', ['id'=>$p->id, 'tab'=>$tab]) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tab" value="{{ $tab }}">

                                                <div class="modal-body">
                                                    <div class="row g-4">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Tahun</label>
                                                            <input type="number" name="year" class="form-control" value="{{ $p->year }}" required>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">Sesi (Pilihan)</label>
                                                            <select name="session" class="form-select">
                                                                <option value="">-</option>
                                                                <option value="1" {{ (string)$p->session==='1' ? 'selected' : '' }}>1</option>
                                                                <option value="2" {{ (string)$p->session==='2' ? 'selected' : '' }}>2</option>
                                                            </select>
                                                            <div class="text-muted small mt-1">Guna bila 2 kali setahun.</div>
                                                        </div>

                                                        <div class="col-md-3">
    <label class="form-label">Tarikh Mula</label>
    <input type="text"
           name="start_date"
           class="form-control date-ikma"
           value="{{ $p->start_date }}"
           required>
</div>

<div class="col-md-3">
    <label class="form-label">Tarikh Tamat</label>
    <input type="text"
           name="end_date"
           class="form-control date-ikma"
           value="{{ $p->end_date }}"
           required>
</div>

                                                        <div class="col-md-3">
                                                            <label class="form-label">Tempoh Aktif</label>
                                                            <div class="form-check form-switch mt-2">
                                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_{{ $p->id }}" {{ $p->is_active ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="is_active_{{ $p->id }}">
                                                                    Set sebagai tempoh aktif
                                                                </label>
                                                            </div>
                                                            <div class="text-muted small mt-1">
                                                                Jika ON, tempoh lain ({{ $type }}) auto OFF.
                                                            </div>
                                                        </div>

                                                        <div class="col-12">
                                                            <label class="form-label">Nota (Pilihan)</label>
                                                            <textarea name="note" class="form-control" rows="2">{{ $p->note }}</textarea>
                                                        </div>
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
                                    <td colspan="6" class="text-center text-muted py-6">
                                        Tiada tempoh lagi. Sila tambah tempoh {{ $type }}.
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