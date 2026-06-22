@extends('layouts.backend.master')

@section('title')
    Laporan Perisytiharan Harta
@endsection

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="card-title">Carian Perisytiharan Harta</h3>
        </div>

        <form action="{{ route('admin.reporting.harta') }}" method="POST">
            @csrf

            <div class="card-body pt-9 pb-0">
                <div class="row">
                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Nama Pegawai</label>
                        <input type="text" name="staff_name" class="form-control" value="{{ $staff_name }}">
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">No. KP</label>
                        <input type="text" name="ic_no" class="form-control" value="{{ $ic_no }}">
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Cawangan</label>
                        <select name="branch" class="form-select" data-control="select2">
                            <option value="">Semua Cawangan</option>
                            @foreach($branchList as $bl)
                                <option value="{{ $bl->id }}" {{ $branch == $bl->id ? 'selected' : '' }}>
                                    {{ ucwords($bl->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Tahun Pemilikan</label>
                        <select name="year" class="form-select" data-control="select2">
                            <option value="">Semua Tahun</option>
                            @foreach($yearList as $yl)
                                <option value="{{ $yl }}" {{ $year == $yl ? 'selected' : '' }}>
                                    {{ $yl }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Jenis Harta</label>
                        <input type="text" name="type" class="form-control" value="{{ $type }}">
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Status Perisytiharan</label>
                        <select name="declaration_status" class="form-select" data-control="select2">
                            <option value="">Semua Status</option>
                            <option value="DRAFT" {{ $declaration_status == 'DRAFT' ? 'selected' : '' }}>Draf</option>
                            <option value="SUBMITTED" {{ $declaration_status == 'SUBMITTED' ? 'selected' : '' }}>Dihantar</option>
                            <option value="APPROVED" {{ $declaration_status == 'APPROVED' ? 'selected' : '' }}>Disahkan</option>
                            <option value="RETURNED" {{ $declaration_status == 'RETURNED' ? 'selected' : '' }}>Dikembalikan</option>
                        </select>
                    </div>

                    <div class="col-md-4 vals-row mb-4">
                        <label class="form-label">Status Harta</label>
                        <select name="status_harta" class="form-select" data-control="select2">
                            <option value="">Semua Status Harta</option>
                            <option value="AKTIF" {{ $status_harta == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                            <option value="DILUPUSKAN" {{ $status_harta == 'DILUPUSKAN' ? 'selected' : '' }}>Dilupuskan</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-4">
                        <button type="submit" class="btn btn-success float-end ms-5">Cari</button>
                        <a href="{{ route('admin.reporting.harta') }}" class="btn btn-light float-end">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="card-title">Senarai Perisytiharan Harta</h3>
        </div>

        <div class="card-body pt-9 pb-5">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead>
                        <tr class="fw-bold">
                            <th class="text-nowrap" style="width:50px">Bil</th>
                            <th class="text-nowrap" style="min-width:180px">Nama</th>
                            <th class="text-nowrap" style="min-width:130px">No. KP</th>
                            <th class="text-nowrap" style="min-width:160px">Cawangan</th>
                            <th class="text-nowrap" style="min-width:140px">Pemilik Harta</th>
                            <th class="text-nowrap" style="min-width:130px">Jenis Harta</th>
                            <th style="min-width:220px">Keterangan</th>
                            <th class="text-nowrap" style="min-width:120px">Nilai</th>
                            <th class="text-nowrap" style="min-width:150px">Sumber Kewangan</th>
                            <th class="text-nowrap" style="min-width:130px">Tarikh Pemilikan</th>
                            <th class="text-nowrap" style="min-width:160px">Status Perisytiharan</th>
                            <th class="text-nowrap" style="min-width:120px">Status Harta</th>
                            @if($status_harta != 'AKTIF')
    <th class="text-nowrap" style="min-width:150px">Pelupusan</th>
@endif
                            <th class="text-nowrap" style="min-width:150px">Tarikh Hantar</th>
                            <th class="text-nowrap" style="min-width:150px">Tarikh Disahkan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if(count($hartaList) > 0)
                            @foreach($hartaList as $index => $harta)
                                <tr>
                                    <td class="text-nowrap">{{ $index + 1 }}</td>
                                    <td class="text-start">{{ ucwords($harta->name ?? '-') }}</td>
                                    <td class="text-nowrap">{{ $harta->ic_no ?? '-' }}</td>
                                    <td>{{ $harta->branch_name ?? '-' }}</td>

                                    <td>
                                        @if($harta->owner_type == 'self')
                                            Sendiri
                                        @elseif($harta->owner_name)
                                            {{ $harta->owner_name }}
                                            <br>
                                            <small>{{ $harta->owner_relation }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>{{ $harta->type ?? '-' }}</td>
                                    <td class="text-start">{{ $harta->description ?? '-' }}</td>
                                    <td class="text-nowrap">RM {{ number_format($harta->value ?? 0, 2) }}</td>
                                    <td>{{ $harta->financial_source ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $harta->year ?? '-' }}</td>

                                    <td class="text-nowrap">
                                        @if($harta->declaration_status == 'APPROVED')
                                            <span class="badge badge-light-success">Disahkan</span>
                                        @elseif($harta->declaration_status == 'SUBMITTED')
                                            <span class="badge badge-light-primary">Dihantar</span>
                                        @elseif($harta->declaration_status == 'RETURNED')
                                            <span class="badge badge-light-warning">Dikembalikan</span>
                                        @else
                                            <span class="badge badge-light-secondary">Draf</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        @if($harta->disposal_status)
                                            <span class="badge badge-light-danger">Dilupuskan</span>
                                        @else
                                            <span class="badge badge-light-success">Aktif</span>
                                        @endif
                                    </td>

                                    @if($status_harta != 'AKTIF')
    <td class="text-nowrap">
        @if($harta->disposal_status)
            {{ $harta->disposal_method ?? '-' }}
            <br>
            <small>{{ $harta->disposal_date ?? '-' }}</small>
        @else
            -
        @endif
    </td>
@endif

                                    <td class="text-nowrap">{{ $harta->submitted_at ?? '-' }}</td>
                                    <td class="text-nowrap">{{ $harta->approved_at ?? '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="16">Tiada Rekod Ditemui</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection