<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perisytiharan Harta</title>

    <style>

        body{
            font-family: DejaVu Sans;
            font-size:10px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,td{
            border:1px solid black;
            padding:4px;
            text-align:center;
        }

        th{
            background:#f2f2f2;
        }

    </style>
</head>
<body>

<h2 style="text-align:center">
    Laporan Perisytiharan Harta
</h2>

<table>

    <thead>
    <tr>
        <th>Bil</th>
        <th>Nama</th>
        <th>No KP</th>
        <th>Cawangan</th>
        <th>Pemilik</th>
        <th>Jenis Harta</th>
        <th>Nilai</th>
        <th>Sumber Kewangan</th>
        <th>Tarikh Pemilikan</th>
        <th>Status Perisytiharan</th>
        <th>Status Harta</th>

        @if($status_harta != 'AKTIF')
            <th>Pelupusan</th>
        @endif

    </tr>
    </thead>

    <tbody>

    @foreach($hartaList as $i => $harta)

        <tr>

            <td>{{ $i+1 }}</td>

            <td>{{ ucwords($harta->name ?? '-') }}</td>

            <td>{{ $harta->ic_no }}</td>

            <td>{{ $harta->branch_name }}</td>

            <td>
                @if($harta->owner_type=='self')
                    Sendiri
                @else
                    {{ $harta->owner_name }}
                @endif
            </td>

            <td>{{ $harta->type }}</td>

            <td>
                RM {{ number_format($harta->value,2) }}
            </td>

            <td>{{ $harta->financial_source }}</td>

            <td>{{ $harta->year }}</td>

            <td>{{ $harta->declaration_status }}</td>

            <td>
                {{ $harta->disposal_status ? 'Dilupuskan' : 'Aktif' }}
            </td>

            @if($status_harta != 'AKTIF')

                <td>

                    @if($harta->disposal_status)

                        {{ $harta->disposal_method }}

                        <br>

                        {{ $harta->disposal_date }}

                    @else

                        -

                    @endif

                </td>

            @endif

        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>