@extends('layouts.backend.master')

@section('title','Admin | Senarai Penilaian Prestasi')

@section('content')
@php
    use App\Helpers\PerformanceHelper;
@endphp


<div class="card">
    <div class="card-body">

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-6">
            <div>
                <h3 class="mb-1">Senarai Penilaian Prestasi</h3>
                <div class="text-muted">Admin view & urus senarai (bukan isi borang).</div>
            </div>
        </div>

        <div class="text-muted small mb-4">
            Penjanaan penilaian akan dibuat untuk semua lantikan yang belum mempunyai penilaian.
        </div>

        {{-- ✅ TAB LNPT / SKT --}}
        @php
            $tabValue  = $tab ?? request('tab', 'lnpt');
            $tabValue  = strtolower((string)$tabValue);
            $typeValue = $type ?? ($tabValue === 'skt' ? 'SKT' : 'LNPT');
            $isSkt     = $tabValue === 'skt';
        @endphp

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link {{ $tabValue === 'lnpt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.evaluations.index', ['tab' => 'lnpt']) }}">
                    LNPT
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tabValue === 'skt' ? 'active' : '' }}"
                   href="{{ route('admin.performance.evaluations.index', ['tab' => 'skt']) }}">
                    SKT
                </a>
            </li>
        </ul>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(!empty($message))
            <div class="alert alert-warning">{{ $message }}</div>
        @endif

        @if($period)
            <form method="GET" action="{{ route('admin.performance.evaluations.index') }}" class="mb-6">
                {{-- ✅ kekalkan tab bila submit/filter --}}
                <input type="hidden" name="tab" value="{{ $tabValue }}">

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Tempoh (Tahun)</label>
                        <select name="period_id" class="form-select" onchange="this.form.submit()">
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" {{ $p->id == $period->id ? 'selected' : '' }}>
                                    {{ $p->year }}
                                    {{ $p->is_active ? '(AKTIF)' : '' }}
                                    {{ !empty($p->session) ? (' / Sesi '.$p->session) : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>

                        {{-- ✅ LNPT vs SKT: options berbeza --}}
                        <select name="status" class="form-select" onchange="this.form.submit()">

    <option value="ALL" {{ $status=='ALL'?'selected':'' }}>
        Semua
    </option>

    <option value="BELUM_JANA" {{ $status=='BELUM_JANA'?'selected':'' }}>
        {{ PerformanceHelper::statusLabel('BELUM_JANA') }}
    </option>

    @if($isSkt)

        <option value="DRAFT" {{ $status=='DRAFT'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('DRAFT') }}
        </option>

        <option value="SUBMITTED" {{ $status=='SUBMITTED'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('SUBMITTED') }}
        </option>

        <option value="RETURNED_BY_PPP" {{ $status=='RETURNED_BY_PPP'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('RETURNED_BY_PPP') }}
        </option>

        <option value="PPP_REVIEWED" {{ $status=='PPP_REVIEWED'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('PPP_REVIEWED') }}
        </option>

    @else

        <option value="DRAFT" {{ $status=='DRAFT'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('DRAFT') }}
        </option>

        <option value="SUBMITTED" {{ $status=='SUBMITTED'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('SUBMITTED') }}
        </option>

        <option value="PPP_SCORED" {{ $status=='PPP_SCORED'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('PPP_SCORED') }}
        </option>

        <option value="PPK_APPROVED" {{ $status=='PPK_APPROVED'?'selected':'' }}>
            {{ PerformanceHelper::statusLabel('PPK_APPROVED') }}
        </option>

    @endif

</select>
                    </div>

                    {{-- ✅ Carian IC (PYD) --}}
                    <div class="col-md-3">
                        <label class="form-label">Carian IC (PYD)</label>
                        <input type="text"
                               name="ic"
                               value="{{ $ic ?? request('ic') }}"
                               class="form-control"
                               placeholder="Contoh: 000000000000">
                    </div>

                    <div class="col-md-2 text-md-end">
                        <button type="submit" class="btn btn-light">
                            Cari
                        </button>
                    </div>

                    <div class="col-md-12 text-md-end">
                        <button form="bulk-form" class="btn btn-light-primary"
                                onclick="return confirm('Bulk jana untuk semua PYD yang ada lantikan?')">
                            Jana Penilaian {{ $typeValue }}
                        </button>
                    </div>
                </div>
            </form>

            <form id="bulk-form" method="POST" action="{{ route('admin.performance.evaluations.bulk-generate') }}">
                @csrf
                <input type="hidden" name="period_id" value="{{ $period->id }}">
                <input type="hidden" name="tab" value="{{ $tabValue }}">
            </form>

            <div class="table-responsive">
                <table class="table table-row-bordered align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th style="width:60px;">Bil.</th>
                            <th>PYD</th>
                            <th style="width:160px;">IC</th>
                            <th>PPP</th>
                            <th>PPK</th>
                            <th style="width:160px;">Status</th>
                            <th style="width:140px;">Tarikh Hantar</th>
                            <th style="width:140px;">Tarikh Sah</th>

                            {{-- ✅ LNPT sahaja: Total + PPSM --}}
                            @if(!$isSkt)
                                <th style="width:110px;">Total</th>
                                <th style="width:110px;">PPSM</th>
                            @endif

                            <th style="width:120px;" class="text-end">Tindakan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rows as $i => $r)
                            @php
                                $eval = $r->evaluation ?? null;

                                // ✅ status: untuk SKT controller dah map $r->status = computed
                                $statusText = $r->status ?? ($eval->status ?? 'BELUM_JANA');

                                // ✅ tarikh: controller dah map ikut type (LNPT guna submitted_at, SKT guna skt_submitted_at)
                                $submittedAt = $r->submitted_at ?? ($eval->submitted_at ?? null);
                                $approvedAt  = $r->ppk_approved_at ?? ($eval->ppk_approved_at ?? null);

                                // ✅ LNPT: score
                                $totalScore = $r->ppk_total_score ?? ($eval->ppk_total_score ?? null);
                                if ($totalScore === null) $totalScore = $r->ppp_total_score ?? ($eval->ppp_total_score ?? null);

                                // ✅ LNPT: PPSM
                                $ppsmScore = $r->ppsm_score ?? ($eval->ppsm_score ?? null);

                                // ✅ IC
                                $icNo = $r->assignment->pydUser->ic_no ?? null;
                            @endphp

                            <tr>
                                <td>{{ $i+1 }}</td>

                                <td>
                                    <strong>{{ $r->assignment->pydUser->name ?? '-' }}</strong>
                                    @if(!empty($r->assignment->pydUser->no_staff))
                                        <div class="text-muted small">{{ $r->assignment->pydUser->no_staff }}</div>
                                    @endif
                                </td>

                                <td class="text-muted">{{ $icNo ?? '-' }}</td>

                                <td>{{ $r->assignment->pppUser->name ?? '-' }}</td>
                                <td>{{ $r->assignment->ppkUser->name ?? '-' }}</td>

                                <td>
                                    <span class="badge badge-light">
                                        {{ $statusText }}
                                    </span>
                                </td>

                                <td class="text-muted">
                                    {{ $submittedAt ? \Carbon\Carbon::parse($submittedAt)->format('d/m/Y') : '-' }}
                                </td>

                                <td class="text-muted">
                                    {{ $approvedAt ? \Carbon\Carbon::parse($approvedAt)->format('d/m/Y') : '-' }}
                                </td>

                                {{-- ✅ LNPT sahaja: Total + PPSM --}}
                                @if(!$isSkt)
                                    <td>
                                        {{ $totalScore !== null ? $totalScore : '-' }}
                                    </td>

                                    <td>
                                        {{ $ppsmScore !== null ? number_format((float)$ppsmScore, 2) : '-' }}
                                    </td>
                                @endif

                                <td class="text-end">
                                    @if(!empty($r->id))
                                        {{-- ✅ bawa tab ke detail supaya boleh kembali tab sama --}}
                                        <a class="btn btn-sm btn-primary"
                                           href="{{ route('admin.performance.evaluations.show', $r->id) }}?tab={{ $tabValue }}">
                                            Detail
                                        </a>
                                    @else
                                        <span class="text-muted">Belum dijana</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                @php
                                    // colspan ikut tab
                                    $colspan = $isSkt ? 9 : 11;
                                @endphp
                                <td colspan="{{ $colspan }}" class="text-center text-muted py-6">
                                    Tiada rekod penilaian untuk tempoh ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection