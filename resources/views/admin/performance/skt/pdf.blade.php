@php
    $pyd = $evaluation->assignment->pydUser ?? null;
    $ppp = $evaluation->assignment->pppUser ?? null;
    $period = $evaluation->period ?? null;

    $sktI   = (array)($evaluation->skt_bahagian_i ?? []);
    $sktII  = (array)($evaluation->skt_bahagian_ii ?? []);
    $sktIII = (array)($evaluation->skt_bahagian_iii ?? []);

    $itemsI = (array)($sktI['items'] ?? []);
    $tambah = (array)($sktII['tambah'] ?? []);
    $gugur  = (array)($sktII['gugur'] ?? []);

    $fmtDate = function ($v) {
        return $v ? optional($v)->format('d/m/Y h:i A') : '-';
    };
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SKT</title>

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
            font-size: 12px;
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
    Laporan SKT dijana pada {{ now()->format('d/m/Y h:i A') }}
</div>

<div class="header">
    <img src="{{ public_path('assets/images/ikmalogo.jpg') }}"
         style="height:70px; margin-bottom:8px;">

    <div class="title">Laporan Sasaran Kerja Tahunan (SKT)</div>
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
        <td style="width: 22%;">Nama PYD</td>
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
        <td>PPP</td>
        <td>:</td>
        <td>{{ $ppp->name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>:</td>
        <td><span class="badge-final">SKT MUKTAMAD / FINAL</span></td>
    </tr>
</table>

<div class="section-title">Ringkasan SKT</div>

<table class="summary">
    <tr>
        <td>
            <div class="label">Bahagian I</div>
            {{ count($itemsI) > 0 ? 'Lengkap' : 'Tiada Data' }}
        </td>
        <td>
            <div class="label">Bahagian II - Tambah</div>
            {{ count($tambah) > 0 ? count($tambah).' Item' : 'Tiada' }}
        </td>
        <td>
            <div class="label">Bahagian II - Gugur</div>
            {{ count($gugur) > 0 ? count($gugur).' Item' : 'Tiada' }}
        </td>
        <td>
            <div class="label">Bahagian III</div>
            {{ !empty($sktIII) ? 'Lengkap' : 'Tiada Data' }}
        </td>
    </tr>
</table>

<div class="section-title">Bahagian I - Sasaran Kerja Tahunan</div>

<table>
    <thead>
        <tr>
            <th style="width: 6%;">Bil.</th>
            <th>Aktiviti / Projek / Tugas</th>
            <th>Petunjuk Prestasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($itemsI as $idx => $item)
            @php
                $aktiviti = trim((string)($item['aktiviti'] ?? ''));
                $petunjuk = trim((string)($item['petunjuk'] ?? ''));
            @endphp

            @if($aktiviti !== '' || $petunjuk !== '')
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $aktiviti !== '' ? $aktiviti : '-' }}</td>
                    <td>{{ $petunjuk !== '' ? $petunjuk : '-' }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="3" style="text-align:center;">Tiada rekod sasaran kerja.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="section-title">Bahagian II - Kajian Semula Pertengahan Tahun</div>

<table>
    <thead>
        <tr>
            <th colspan="3">Aktiviti / Projek / Tugas Yang Ditambah</th>
        </tr>
        <tr>
            <th style="width: 6%;">Bil.</th>
            <th>Aktiviti / Projek / Tugas</th>
            <th>Petunjuk Prestasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($tambah as $idx => $item)
            @php
                $aktiviti = trim((string)($item['aktiviti'] ?? ''));
                $petunjuk = trim((string)($item['petunjuk'] ?? ''));
            @endphp

            @if($aktiviti !== '' || $petunjuk !== '')
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $aktiviti !== '' ? $aktiviti : '-' }}</td>
                    <td>{{ $petunjuk !== '' ? $petunjuk : '-' }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="3" style="text-align:center;">Tiada aktiviti ditambah.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<br>

<table>
    <thead>
        <tr>
            <th colspan="2">Aktiviti / Projek / Tugas Yang Digugurkan</th>
        </tr>
        <tr>
            <th style="width: 6%;">Bil.</th>
            <th>Aktiviti / Projek / Tugas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($gugur as $idx => $item)
            @php
                $aktiviti = trim((string)($item['aktiviti'] ?? ''));
            @endphp

            @if($aktiviti !== '')
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $aktiviti }}</td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="2" style="text-align:center;">Tiada aktiviti digugurkan.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="page-break"></div>

<div class="header">
    <img src="{{ public_path('assets/images/ikmalogo.jpg') }}"
         style="height:60px; margin-bottom:8px;">

    <div class="title">Laporan Sasaran Kerja Tahunan (SKT)</div>
    <div class="subtitle">Bahagian III dan Pengesahan</div>
</div>

<div class="section-title">Bahagian III - Ulasan Pencapaian Akhir Tahun</div>

<table>
    <tr>
        <th style="width: 30%;">Perkara</th>
        <th>Ulasan</th>
    </tr>
    <tr>
        <td>Ulasan PYD</td>
        <td>{{ $sktIII['ulasan_pyd'] ?? '-' }}</td>
    </tr>
    <tr>
        <td>Ulasan PPP</td>
        <td>{{ $sktIII['ulasan_ppp'] ?? '-' }}</td>
    </tr>
</table>
</body>
</html>