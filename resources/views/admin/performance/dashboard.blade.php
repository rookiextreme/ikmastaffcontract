@extends('layouts.backend.master')

@section('title', 'Dashboard Prestasi')

@section('content')
<div class="container-fluid">

    @php
        $completedCount = $isSkt
            ? ($stats['ppp_reviewed'] ?? 0)
            : (($stats['ppk_approved'] ?? 0) + ($stats['final'] ?? 0));

        $progressPercent = ($stats['total'] ?? 0) > 0
            ? round(($completedCount / $stats['total']) * 100)
            : 0;

        if ($isSkt) {
            $mainStatusLabel = 'Disahkan PPP';
            $mainStatusValue = $stats['ppp_reviewed'] ?? 0;
        } else {
            if (($stats['final'] ?? 0) > 0) {
                $mainStatusLabel = 'Muktamad';
                $mainStatusValue = $stats['final'];
            } elseif (($stats['ppk_approved'] ?? 0) > 0) {
                $mainStatusLabel = 'PPK Disahkan';
                $mainStatusValue = $stats['ppk_approved'];
            } elseif (($stats['ppp_scored'] ?? 0) > 0) {
                $mainStatusLabel = 'PPP Selesai';
                $mainStatusValue = $stats['ppp_scored'];
            } elseif (($stats['submitted'] ?? 0) > 0) {
                $mainStatusLabel = 'Telah Dihantar';
                $mainStatusValue = $stats['submitted'];
            } else {
                $mainStatusLabel = 'Draf';
                $mainStatusValue = $stats['draft'] ?? 0;
            }
        }
    @endphp

    <div class="card mb-6 border-0 shadow-sm">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-4">
            <div>
                <div class="d-flex align-items-center gap-3 mb-2">
                    <h3 class="mb-0">Dashboard Prestasi</h3>
                    <span class="badge badge-light-primary">
                        {{ $tab }}
                    </span>
                    @if($period && ($period->is_active ?? 0) == 1)
                        <span class="badge badge-light-success">Tempoh Aktif</span>
                    @endif
                </div>
                <div class="text-muted">
                    Paparan ringkas status penilaian prestasi untuk pentadbir
                </div>
            </div>

            @if($period)
                <div class="text-md-end">
                    <div class="fw-semibold text-dark">
                        {{ $tab }} • Tahun {{ $period->year }}
                        @if(!empty($period->session))
                            / Sesi {{ $period->session }}
                        @endif
                    </div>
                    <div class="text-muted fs-7">
                        Kemajuan semasa: {{ $progressPercent }}%
                    </div>
                </div>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('admin.performance.dashboard') }}" class="card mb-6 border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jenis Penilaian</label>
                    <select name="tab" class="form-select" onchange="this.form.submit()">
                        <option value="LNPT" {{ $tab === 'LNPT' ? 'selected' : '' }}>LNPT</option>
                        <option value="SKT" {{ $tab === 'SKT' ? 'selected' : '' }}>SKT</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tempoh Penilaian</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Tempoh --</option>
                        @foreach($periods as $p)
                            <option value="{{ $p->id }}"
                                {{ (string)optional($period)->id === (string)$p->id ? 'selected' : '' }}>
                                {{ $p->year }} @if(!empty($p->session)) - Sesi {{ $p->session }} @endif
                                @if(($p->is_active ?? 0) == 1) (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <a href="{{ route('admin.performance.dashboard', ['tab' => $tab]) }}" class="btn btn-light-primary">
                        Reset
                    </a>
                </div>
            </div>
        </div>
    </form>

    @if(!$period)
        <div class="alert alert-warning">
            Tiada tempoh penilaian dijumpai untuk jenis <strong>{{ $tab }}</strong>.
            Sila cipta atau aktifkan tempoh penilaian terlebih dahulu.
        </div>
    @else
        <div class="mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h4 class="mb-1">
                        {{ $tab }} - Tahun {{ $period->year }}
                        @if(!empty($period->session))
                            / Sesi {{ $period->session }}
                        @endif
                    </h4>
                    <div class="text-muted">
                        Ringkasan status penilaian bagi tempoh yang dipilih
                    </div>
                </div>

                <div style="min-width: 260px;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted fs-7">Kemajuan Proses</span>
                        <span class="fw-bold fs-7">{{ $progressPercent }}%</span>
                    </div>
                    <div class="progress h-8px bg-light-primary">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ $progressPercent }}%;"
                             aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5 mb-6">
            <div class="col-md-6 col-xl-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted mb-2">Jumlah Penilaian</div>
                        <div class="fs-2hx fw-bold text-dark">{{ $stats['total'] }}</div>
                        <div class="text-muted fs-7 mt-2">Keseluruhan rekod penilaian</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted mb-2">Draf</div>
                        <div class="fs-2hx fw-bold text-warning">{{ $stats['draft'] }}</div>
                        <div class="text-muted fs-7 mt-2">Belum dihantar</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted mb-2">Telah Dihantar</div>
                        <div class="fs-2hx fw-bold text-info">{{ $stats['submitted'] }}</div>
                        <div class="text-muted fs-7 mt-2">Menunggu semakan seterusnya</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="text-muted mb-2">
                            {{ $isSkt ? 'Disahkan PPP' : 'PPP Selesai' }}
                        </div>
                        <div class="fs-2hx fw-bold text-primary">
                            {{ $isSkt ? $stats['ppp_reviewed'] : $stats['ppp_scored'] }}
                        </div>
                        <div class="text-muted fs-7 mt-2">
                            {{ $isSkt ? 'SKT selesai semakan PPP' : 'LNPT selesai dinilai PPP' }}
                        </div>
                    </div>
                </div>
            </div>

            @if($isSkt)
                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted mb-2">SKT Lengkap</div>
                            <div class="fs-2hx fw-bold text-success">{{ $stats['ppp_reviewed'] }}</div>
                            <div class="text-muted fs-7 mt-2">Selesai untuk aliran SKT</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted mb-2">PPK Sahkan</div>
                            <div class="fs-2hx fw-bold text-success">{{ $stats['ppk_approved'] }}</div>
                            <div class="text-muted fs-7 mt-2">Telah disahkan oleh PPK</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted mb-2">Muktamad</div>
                            <div class="fs-2hx fw-bold text-dark">{{ $stats['final'] }}</div>
                            <div class="text-muted fs-7 mt-2">Selesai sepenuhnya</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="row g-5">
            <div class="{{ $isSkt ? 'col-xl-8' : 'col-xl-7' }}">
                <div class="card mb-6 border-0 shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Graf Status Penilaian</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceStatusChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            <div class="{{ $isSkt ? 'col-xl-4' : 'col-xl-5' }}">
                <div class="card mb-6 border-0 shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Ringkasan Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-5 p-4 rounded bg-light-primary">
                            <div class="text-muted fs-7 mb-1">Status Utama Tempoh Ini</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-bold text-dark">{{ $mainStatusLabel }}</div>
                                <div class="fs-2 fw-bold text-primary">{{ $mainStatusValue }}</div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th>Status</th>
                                        <th class="text-end">Bilangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>DRAFT</td>
                                        <td class="text-end">{{ $stats['draft'] }}</td>
                                    </tr>
                                    <tr>
                                        <td>SUBMITTED</td>
                                        <td class="text-end">{{ $stats['submitted'] }}</td>
                                    </tr>

                                    @if($isSkt)
                                        <tr>
                                            <td>PPP_REVIEWED</td>
                                            <td class="text-end">{{ $stats['ppp_reviewed'] }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td>PPP_SCORED</td>
                                            <td class="text-end">{{ $stats['ppp_scored'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>PPK_APPROVED</td>
                                            <td class="text-end">{{ $stats['ppk_approved'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>FINAL</td>
                                            <td class="text-end">{{ $stats['final'] }}</td>
                                        </tr>
                                    @endif

                                    <tr class="fw-bold">
                                        <td>JUMLAH</td>
                                        <td class="text-end">{{ $stats['total'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Aktiviti Penilaian Terkini</h3>
                <span class="badge badge-light-secondary">{{ $recentEvaluations->count() }} Rekod</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-hover align-middle">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th width="50">Bil</th>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Kemaskini Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEvaluations as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $row->user->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary">
                                            {{ $row->period->type ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($row->status) {
                                                'DRAFT' => 'warning',
                                                'SUBMITTED' => 'info',
                                                'PPP_SCORED' => 'primary',
                                                'PPP_REVIEWED' => 'primary',
                                                'PPK_APPROVED' => 'success',
                                                'FINAL' => 'dark',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-light-{{ $badgeClass }}">
                                            {{ $row->status ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ optional($row->updated_at)->format('d/m/Y h:i A') ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-6">
                                        Tiada data penilaian untuk tempoh ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
@if($period)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('performanceStatusChart');

    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($statusChartLabels),
                datasets: [{
                    label: 'Bilangan',
                    data: @json($statusChartValues),
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
@endif
@endsection