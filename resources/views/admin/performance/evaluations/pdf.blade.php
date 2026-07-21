@php
    $pyd = $evaluation->assignment->pydUser ?? null;
    $ppp = $evaluation->assignment->pppUser ?? null;
    $ppk = $evaluation->assignment->ppkUser ?? null;
    $period = $evaluation->period ?? null;
    $pydGroup = strtoupper(
    trim((string) (
        $pydGroup
        ?? $evaluation->assignment->pyd_group
        ?? 'BC'
    ))
);

$pydGroupLabel = match ($pydGroup) {
    'A'  => 'Kumpulan Pengurusan & Profesional (A)',
    'BC' => 'Kumpulan Perkhidmatan Sokongan (B/C)',
    default => '-',
};

    $pppTotal = $evaluation->ppp_total_score ?? 0;
    $ppkTotal = $evaluation->ppk_total_score ?? 0;
    $average  = ($pppTotal > 0 && $ppkTotal > 0) ? (($pppTotal + $ppkTotal) / 2) : 0;
    $ppsm     = $evaluation->ppsm_score ?? null;

    $gred = '-';
    if ($ppsm !== null) {
        if ($ppsm >= 90) $gred = 'Cemerlang';
        elseif ($ppsm >= 80) $gred = 'Sangat Baik';
        elseif ($ppsm >= 70) $gred = 'Baik';
        elseif ($ppsm >= 60) $gred = 'Memuaskan';
        else $gred = 'Kurang Memuaskan';
    }

    $fmt = fn($v) => number_format((float)$v, 2);
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penilaian Prestasi</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1f4e79;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1f4e79;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 12px;
            margin-top: 4px;
        }

        .section-title {
            background: #1f4e79;
            color: white;
            padding: 7px 9px;
            font-weight: bold;
            margin-top: 16px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1f5f9;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            vertical-align: top;
        }

        .no-border td {
            border: none;
            padding: 4px 2px;
        }

        .summary td {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }

        .summary .label {
            font-size: 10px;
            color: #555;
            font-weight: normal;
        }

        .badge-final {
            display: inline-block;
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            padding: 4px 8px;
            font-weight: bold;
        }

        .muted {
            color: #666;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="footer">
    Laporan dijana pada {{ now()->format('d/m/Y h:i A') }}
</div>

<div class="header">
    <img src="{{ public_path('assets/images/ikmalogo.jpg') }}"
         style="height:70px; margin-bottom:8px;">

    <div class="title">Laporan Penilaian Prestasi</div>
    <div class="subtitle">Institut Koperasi Malaysia</div>
    <div class="subtitle">
        Tahun {{ $period->year ?? '-' }}
        @if(!empty($period->session))
            | Sesi {{ $period->session }}
        @endif
    </div>
</div>

<div class="section-title">Maklumat Pegawai</div>

<table class="no-border">
    <tr>
        <td style="width: 20%;">Nama PYD</td>
        <td style="width: 2%;">:</td>
        <td><strong>{{ $pyd->name ?? '-' }}</strong></td>
    </tr>
    <tr>
        <td>No. Kad Pengenalan</td>
        <td>:</td>
        <td>{{ $pyd->ic_no ?? '-' }}</td>
    </tr>
    <tr>
        <td>Jawatan</td>
        <td>:</td>
        <td>{{ $pyd->staffPosition->position->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Gred</td>
        <td>:</td>
        <td>{{ $pyd->staffPosition->grade->name ?? '-' }}</td>
    </tr>
    <tr>
    <td>Kumpulan Perkhidmatan</td>
    <td>:</td>
    <td>{{ $pydGroupLabel }}</td>
</tr>
    <tr>
        <td>PPP</td>
        <td>:</td>
        <td>{{ $ppp->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>PPK</td>
        <td>:</td>
        <td>{{ $ppk->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>:</td>
        <td><span class="badge-final">MUKTAMAD / FINAL</span></td>
    </tr>
</table>

<div class="section-title">Ringkasan Markah</div>

<table class="summary">
    <tr>
        <td>
            <div class="label">Jumlah PPP</div>
            {{ $fmt($pppTotal) }}
        </td>
        <td>
            <div class="label">Jumlah PPK</div>
            {{ $fmt($ppkTotal) }}
        </td>
        <td>
            <div class="label">Purata</div>
            {{ $fmt($average) }}
        </td>
        <td>
            <div class="label">PPSM</div>
            {{ $ppsm !== null ? $fmt($ppsm) : '-' }}
        </td>
        <td>
            <div class="label">Gred</div>
            {{ $gred }}
        </td>
    </tr>
</table>

@foreach(['III','IV','V','VI'] as $sec)
    @php
        $items = $itemsBySection[$sec] ?? collect();
        $meta = $sectionMeta[$sec] ?? [];
        $weight = $meta['weight'] ?? null;

        $sumPPP = 0;
        $sumPPK = 0;
        $maxScore = match ($sec) {
    'III' => 50,
    'IV'  => 30,
    'V'   => $pydGroup === 'A' ? 50 : 40,
    'VI'  => 10,
    default => max(1, (int) $items->count() * 10),
};

        foreach ($items as $item) {
            $score = $scores->get($item->id);
            $sumPPP += (int)($score->ppp_score ?? 0);
            $sumPPK += (int)($score->ppk_score ?? 0);
        }
        $weightedPPP = ($maxScore > 0 && $weight)
    ? ($sumPPP / $maxScore) * $weight
    : 0;

$weightedPPK = ($maxScore > 0 && $weight)
    ? ($sumPPK / $maxScore) * $weight
    : 0;
    @endphp

    <div class="section-title">
        Bahagian {{ $sec }}
        @if($weight)
            - Wajaran {{ $weight }}%
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">Bil.</th>
                <th>Kriteria</th>
                <th style="width: 12%;">PPP</th>
                <th style="width: 12%;">PPK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                @php
                    $score = $scores->get($item->id);
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->name ?? '-' }}</strong>
                        @if($sec !== 'V' && !empty($item->description))
    <div class="muted">{{ $item->description }}</div>
@endif
                    </td>
                    <td style="text-align:center;">{{ $score->ppp_score ?? '-' }}</td>
                    <td style="text-align:center;">{{ $score->ppk_score ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">Tiada item.</td>
                </tr>
            @endforelse

            <tr>
    <td colspan="2">
        <strong>Jumlah markah mengikut wajaran</strong>
    </td>

    <td style="text-align:center;">
        <strong>{{ $sumPPP }} / {{ $maxScore }}</strong>

        @if($weight)
            <div class="muted">
                {{ $sumPPP }} / {{ $maxScore }}
                × {{ $weight }}
                = {{ number_format($weightedPPP, 2) }}
            </div>
        @endif
    </td>

    <td style="text-align:center;">
        <strong>{{ $sumPPK }} / {{ $maxScore }}</strong>

        @if($weight)
            <div class="muted">
                {{ $sumPPK }} / {{ $maxScore }}
                × {{ $weight }}
                = {{ number_format($weightedPPK, 2) }}
            </div>
        @endif
    </td>
</tr>
        </tbody>
    </table>
@endforeach

<div class="page-break"></div>

<div class="header">
    <div class="title">Laporan Penilaian Prestasi</div>
    <div class="subtitle">Bahagian Ulasan dan Pengesahan</div>
</div>

<div class="section-title">Bahagian VII - Jumlah Markah Keseluruhan</div>

<table>
    <tr>
        <th>Jumlah PPP</th>
        <th>Jumlah PPK</th>
        <th>Purata</th>
        <th>PPSM</th>
        <th>Gred</th>
    </tr>
    <tr>
        <td style="text-align:center;">{{ $fmt($pppTotal) }}</td>
        <td style="text-align:center;">{{ $fmt($ppkTotal) }}</td>
        <td style="text-align:center;">{{ $fmt($average) }}</td>
        <td style="text-align:center;">{{ $ppsm !== null ? $fmt($ppsm) : '-' }}</td>
        <td style="text-align:center;">{{ $gred }}</td>
    </tr>
</table>

<div class="section-title">Bahagian VIII - Ulasan Keseluruhan PPP</div>

<table>
    <tr>
        <th style="width: 30%;">Perkara</th>
        <th>Maklumat</th>
    </tr>
    <tr>
        <td>Tempoh PYD bertugas di bawah pengawasan</td>
        <td>
            {{ $evaluation->ppp_supervise_years ?? '-' }} tahun
            {{ $evaluation->ppp_supervise_months ?? '-' }} bulan
        </td>
    </tr>
    <tr>
        <td>Prestasi Keseluruhan</td>
        <td>{{ $evaluation->ppp_overall_performance ?? '-' }}</td>
    </tr>
    <tr>
        <td>Kemajuan Kerjaya</td>
        <td>{{ $evaluation->ppp_career_progress ?? '-' }}</td>
    </tr>
</table>

<div class="section-title">Bahagian IX - Ulasan Keseluruhan PPK</div>

<table>
    <tr>
        <th style="width: 30%;">Perkara</th>
        <th>Maklumat</th>
    </tr>
    <tr>
        <td>Tempoh PYD bertugas di bawah pengawasan</td>
        <td>
            {{ $evaluation->ppk_supervise_years ?? '-' }} tahun
            {{ $evaluation->ppk_supervise_months ?? '-' }} bulan
        </td>
    </tr>
    <tr>
        <td>Ulasan PPK</td>
        <td>{{ $evaluation->ppk_comment ?? '-' }}</td>
    </tr>
</table>

</body>
</html>