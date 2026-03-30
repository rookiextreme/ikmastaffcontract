@php
    $e = $evaluation;
    $data = (array)($e->skt_bahagian_i ?? []);
    $items = (array)($data['items'] ?? []);
    if(count($items) < 5){
        for($i=count($items); $i<5; $i++) $items[] = ['aktiviti'=>'','petunjuk'=>''];
    }
@endphp

<form method="POST" action="{{ route('staff.performance.skt.save') }}">
    @csrf
    <input type="hidden" name="bahagian" value="I">

    <div class="card border mb-6">
        <div class="card-header">
            <h4 class="card-title mb-0">BAHAGIAN I - Penetapan Sasaran Kerja Tahunan</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px;">Bil.</th>
                            <th>Ringkasan Aktiviti / Projek<br><span class="text-muted small">(Senaraikan aktiviti / projek)</span></th>
                            <th>Petunjuk Prestasi<br><span class="text-muted small">(Kuantiti / Kualiti / Masa / Kos)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $row)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>
                                    <textarea class="form-control" rows="2"
                                        name="skt_bahagian_i[items][{{ $i }}][aktiviti]">{{ $row['aktiviti'] ?? '' }}</textarea>
                                </td>
                                <td>
                                    <textarea class="form-control" rows="2"
                                        name="skt_bahagian_i[items][{{ $i }}][petunjuk]">{{ $row['petunjuk'] ?? '' }}</textarea>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="submit" class="btn btn-primary">Simpan</button>

                <button type="submit" formaction="{{ route('staff.performance.skt.submit') }}"
                        class="btn btn-light-primary"
                        onclick="return confirm('Hantar SKT kepada PPP?')">
                    Hantar kepada PPP
                </button>
            </div>
        </div>
    </div>
</form>