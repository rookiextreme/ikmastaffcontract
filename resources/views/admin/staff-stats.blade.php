@extends('layouts.backend.master')
@section('title','Maklumat Kakitangan')

@section('content')
<div class="row g-5">
    {{-- ===================== CARTA STATISTIK ===================== --}}
    <div class="col-12">
        <div class="card border">
            <div class="card-header">
                <h3 class="card-title mb-0">Statistik Kakitangan</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="chartBranches" height="250"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="chartPositions" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TAPISAN & SENARAI ===================== --}}
    <div class="col-12">
        <div class="card border">
            <div class="card-header">
                <h3 class="card-title mb-0">Senarai Staf Mengikut Tapisan</h3>
            </div>

            <div class="card-body">
                {{-- TAPISAN --}}
                <div class="row mb-4">
                    {{-- PENEMPATAN --}}
                    <div class="col-md-4">
                        <label class="form-label">Penempatan</label>
                        <select id="branchSelect" class="form-select" data-control="select2">
                            <option value="" selected>Semua Penempatan</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">
                                    {{ ucwords(strtolower($b->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- JAWATAN --}}
                    <div class="col-md-4">
                        <label class="form-label">Jawatan</label>
                        <select id="positionSelect" class="form-select" data-control="select2">
                            <option value="" selected>Semua Jawatan</option>
                            @foreach($positions as $p)
                                <option value="{{ $p->id }}">
                                    {{ ucwords(strtolower($p->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- UNIT (✅ BARU) --}}
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <select id="unitSelect" class="form-select" data-control="select2">
                            <option value="" selected>Semua Unit</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">
                                    {{ ucwords(strtolower($u->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BUTANG TAPIS --}}
                    <div class="col-md-2 d-flex align-items-end mt-3">
                        <button id="btnFilter" class="btn btn-primary w-100">
                            Tapis
                        </button>
                    </div>
                </div>

                {{-- JADUAL --}}
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nama Staf</th>
                                <th>Jawatan</th>
                                <th>Unit</th> {{-- ✅ BARU --}}
                                <th>Penempatan</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Sila pilih tapisan di atas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ===================== DATA CARTA ===================== */
    const branchLabels   = @json($branchLabels);
    const branchData     = @json($branchData);
    const positionLabels = @json($positionLabels);
    const positionData   = @json($positionData);

    function colors(n){
        const a=[];
        for(let i=0;i<n;i++){
            a.push(`hsl(${Math.floor(360/n*i)} 70% 60%)`);
        }
        return a;
    }

    new Chart(document.getElementById('chartBranches'), {
        type: 'bar',
        data: {
            labels: branchLabels,
            datasets: [{ data: branchData, backgroundColor: colors(branchLabels.length) }]
        },
        options: {
            plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true } }
        }
    });

    new Chart(document.getElementById('chartPositions'), {
        type: 'bar',
        data: {
            labels: positionLabels,
            datasets: [{ data: positionData, backgroundColor: colors(positionLabels.length) }]
        },
        options: {
            plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true } }
        }
    });

    /* ===================== INIT SELECT2 ===================== */
    $('#branchSelect').select2({ width: '100%', minimumResultsForSearch: 0 });
    $('#positionSelect').select2({ width: '100%', minimumResultsForSearch: 0 });
    $('#unitSelect').select2({ width: '100%', minimumResultsForSearch: 0 });

    // default semua kosong
    $('#branchSelect').val('').trigger('change.select2');
    $('#positionSelect').val('').trigger('change.select2');
    $('#unitSelect').val('').trigger('change.select2');

    /* ===================== TAPIS (AJAX) ===================== */
    document.getElementById('btnFilter').addEventListener('click', function (e) {
        e.preventDefault();

        const branch_id   = document.getElementById('branchSelect').value;   // "" = Semua
        const position_id = document.getElementById('positionSelect').value; // "" = Semua
        const unit_id     = document.getElementById('unitSelect').value;     // "" = Semua ✅

        const url = `{{ route('admin.staff.stats.filter') }}?branch_id=${encodeURIComponent(branch_id)}&position_id=${encodeURIComponent(position_id)}&unit_id=${encodeURIComponent(unit_id)}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('staffTableBody');
                tbody.innerHTML = '';

                if(data.length === 0){
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Tiada data ditemui.
                            </td>
                        </tr>`;
                } else {
                    data.forEach(s => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${s.staff_name ?? '-'}</td>
                                <td>${s.position_name ?? '-'}</td>
                                <td>${s.unit_name ?? '-'}</td>
                                <td>${s.branch_name ?? '-'}</td>
                            </tr>`;
                    });
                }
            })
            .catch(() => {
                const tbody = document.getElementById('staffTableBody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Ralat semasa memproses tapisan.
                        </td>
                    </tr>`;
            });
    });

});
</script>
@endpush
