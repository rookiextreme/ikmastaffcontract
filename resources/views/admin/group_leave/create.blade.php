@extends('layouts.backend.master')
@section('title','Permohonan Cuti Kelompok')

@section('content')
<div class="card mb-5 mb-xl-10">
    <div class="card-header">
        <h3 class="card-title">Permohonan Baharu - Cuti Kelompok</h3>
        <div class="card-toolbar">
            <a href="{{ route('admin.group_leave.index') }}" class="btn btn-light btn-sm">Kembali</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.group_leave.store') }}" enctype="multipart/form-data" id="groupLeaveForm">
        @csrf
        <div class="card-body">

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Cari Staf --}}
            <div class="mb-4">
                <label class="form-label">Pilih Staf (Nama / No KP / No Staf)</label>
                <input type="text" class="form-control" id="staffSearch" placeholder="Contoh: Ali / 900101-01-1234 / K001">
                <input type="hidden" name="staff_id" id="staff_id" value="{{ old('staff_id') }}">
                @error('staff_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                <div class="list-group mt-2 d-none" id="staffResult"></div>
                <div class="mt-2 text-muted small" id="staffSelectedText"></div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label">Tarikh Mula</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date') }}">
                    @error('start_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label">Tarikh Tamat</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date') }}">
                    @error('end_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label">Bilangan Hari (Auto Kira)</label>
                    <input type="number" name="total_days" id="total_days" class="form-control" value="{{ old('total_days',0) }}" readonly>
                    @error('total_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Upload Borang Manual (PDF/JPG/PNG)</label>
                <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                @error('attachment') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Catatan</label>
                <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                @error('remarks') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

        </div>

        <div class="card-footer d-flex justify-content-end">
            <button class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('jsCustom')
    <script src="{{ asset('js/modules/admin/group_leave/create/init.js') }}?v=1"></script>
    <script src="{{ asset('js/modules/admin/group_leave/create/index.js') }}?v=1"></script>
@endsection
