@extends('layouts.backend.master')
@section('title','Mohon Cuti Bagi Staf')

@section('content')
<div class="card mb-5">
  <div class="card-header">
    <h3 class="card-title">Senarai Permohonan Cuti {{ $staff->name }}</h3>
  </div>
  <div class="card-body">
    @if($activeLeaves->count() == 0)
      <p class="text-muted">Tiada permohonan cuti aktif.</p>
    @else
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Kategori</th>
            <th>Tarikh Mula</th>
            <th>Tarikh Tamat</th>
            <th>Status</th>
            <th>Tindakan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($activeLeaves as $leave)
          <tr>
            <td>{{ $leave->category->name ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}</td>
            <td>{{ $leave->status }}</td>
            <td>
              <form action="{{ route('admin.leave.delete', $leave->id) }}" method="POST" onsubmit="return confirm('Padam permohonan cuti ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm">Padam</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>
</div>

{{-- Borang mohon cuti baru --}}
<div class="card">
  <div class="card-header"><h3 class="card-title">Mohon Cuti Bagi: {{ $staff->name }}</h3></div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.leave.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="staff_id" value="{{ $staff->id }}">

      <div class="mb-3">
        <label class="form-label">Kategori Cuti</label>
        <select class="form-select" name="leave_category_id" required>
          <option value="">-- Pilih --</option>
          @foreach($leaveCategory as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Tarikh Mula</label>
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Tarikh Tamat</label>
          <input type="date" name="end_date" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Lampiran</label>
        <input type="file" name="attachment" class="form-control">
        <div class="form-text text-muted">
          Sila masukkan sijil sakit atau surat temu janji hospital jika berkaitan.
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Sebab</label>
        <textarea name="reason" class="form-control" rows="4"></textarea>
      </div>

      <button class="btn btn-success">Hantar Permohonan</button>
    </form>
  </div>
</div>
@endsection
