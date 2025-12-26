@php
    header("Content-Type: application/vnd.ms-excel");
    header('Content-Disposition: attachment; filename="Sejarah-Jawatan-' . $staff->getUser->name . '.xls"');
    header("Pragma: no-cache");
    header("Expires: 0");
@endphp
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
        <h1 style="text-transform: uppercase">Sejarah Jawatan {{ $staff->getUser->name }}</h1>
    </div>
    <div>
        <table style="width: 100%">
            <thead>
            <tr class="fw-bold fs-6 text-gray-800" style="text-align:center">
                <th style="width: 30%">Penempatan</th>
                <th style="width: 20%">Jawatan</th>
                <th style="width: 10%">Terkini?</th>
            </tr>
            </thead>
            <tbody>
            @if(count($staff->getStaffPositionHistory) > 0)
                @php
                    $x = 0;
                @endphp
                @foreach($staff->getStaffPositionHistory as $gsp)
                    <tr data-id="{{ $gsp->id }}">
                        <td style="text-align: center">
                            {{ $gsp->getBranch->name }}<br>
                            {{ $gsp->start_date ? date('d-m-Y', strtotime($gsp->start_date)) : '-' }}<br>Hingga<br> {{ $gsp->end_date ? date('d-m-Y', strtotime($gsp->end_date)) : '-' }}
                        </td>
                        <td style="text-align: center">
                            @if($gsp->getBranchUnit)
                                {{ $gsp->getBranchUnit?->name }}<br>
                            @endif
                            {{ $gsp->getBranchPosition->getPosition->name }}<br>
                            {{ $gsp->getBranchPosition->getGrade->name }}
                        </td>
                        <td style="text-align: center">
                            @if($gsp->active)
                                <span class="text-success">Aktif</span>
                            @else
                                @if($gsp->end_date)
                                    <span class="text-danger">Tamat</span>
                                @else
                                    <span class="text-warning">-</span>
                                @endif

                            @endif
                        </td>
                    </tr>
                    @php
                        $x++;
                    @endphp
                @endforeach
            @else
                <tr>
                    <td colspan="3" class="text-center">
                        Tiada Sejarah Perkhidmatan
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</body>
</html>
