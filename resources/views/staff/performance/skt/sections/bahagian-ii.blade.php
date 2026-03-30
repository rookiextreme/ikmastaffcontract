@php
    $e = $evaluation;
    $data = (array)($e->skt_bahagian_ii ?? []);
    $tambah = (array)($data['tambah'] ?? []);
    $gugur  = (array)($data['gugur'] ?? []);
@endphp

<form method="POST" action="{{ route('staff.performance.skt.save') }}">
    @csrf
    <input type="hidden" name="bahagian" value="II">

    <div class="card border mb-6">
        <div class="card-header">
            <h4 class="card-title mb-0">BAHAGIAN II - Kajian Semula Sasaran Kerja Tahunan Pertengahan Tahun</h4>
        </div>

        <div class="card-body">
            <h5 class="mb-3">1. Aktiviti / Projek Yang Ditambah</h5>
            <div class="table-responsive mb-5">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">Bil.</th>
                            <th>Ringkasan Aktiviti / Projek</th>
                            <th>Petunjuk Prestasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=0; $i<3; $i++)
                            @php $row = $tambah[$i] ?? ['aktiviti'=>'','petunjuk'=>'']; @endphp
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[tambah][{{ $i }}][aktiviti]">{{ $row['aktiviti'] ?? '' }}</textarea></td>
                                <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[tambah][{{ $i }}][petunjuk]">{{ $row['petunjuk'] ?? '' }}</textarea></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <h5 class="mb-3">2. Aktiviti / Projek Yang Digugurkan</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">Bil.</th>
                            <th>Aktiviti / Projek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i=0; $i<3; $i++)
                            @php $row = $gugur[$i] ?? ['aktiviti'=>'']; @endphp
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[gugur][{{ $i }}][aktiviti]">{{ $row['aktiviti'] ?? '' }}</textarea></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</form>