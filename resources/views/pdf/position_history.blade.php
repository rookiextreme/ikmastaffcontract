<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div style="text-align: center;font-weight: bold">
        <h1 style="text-transform: uppercase">Sejarah Jawatan Staf</h1>
    </div>
    <div style="font-weight: bold;margin-bottom: 1rem;">
        Jumlah Staff: {{ count($staffList) }}
    </div>
    <div>
        <table style="width: 100%">
            <thead>
            <tr>
                <td style="width:5%;text-align: center">Bil</td>
                <td style="width:25%;text-align: center">Nama</td>
                <th style="width:12%;text-align: center">No. KP</th>   {{-- ✅ Tambah kolum baru --}}
                <td style="width:25%;text-align: center">Jawatan</td>
                <td style="width:5%;text-align: center">Gred</td>
                <td style="width:15%;text-align: center">Unit</td> {{-- ✅ TAMBAH --}}
                <td style="width:25%;text-align: center">Cawangan</td>
                <td style="width:10%;text-align: center">Tarikh Lantik</td>
                <td style="width:10%;text-align: center">Tarikh Tamat</td>
            </tr>
            </thead>
            <tbody>
            @if(count($staffList) > 0)
                @foreach($staffList as $index => $sl)
                    <tr>
                        <td style="text-align:center">{{ $index + 1 }}</td>
                        <td>{{ ucwords($sl->name)  }}</td>
                        <td style="text-align: center">{{ $sl->ic_number ?? '-' }}</td> {{-- ✅ Paparkan IC --}}
                        <td>{{ $sl->position }}</td>
                        <td style="text-align: center">{{ $sl->grade }}</td>
                        <td>{{ $sl->unit_name ?? '-' }}</td> {{-- ✅ TAMBAH --}}
                        <td>{{ $sl->branch_name }}</td>
                        <td  style="text-align: center">
                            {{ $sl->start_date ?? '-' }}
                        </td>
                        <td  style="text-align: center">
                            {{ $sl->end_date ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9">Tiada Rekod Ditemui</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</body>
</html>
