@php
    // =========================================================
    // BAHAGIAN III - PENGHASILAN KERJA (Wajaran 50%)
    // =========================================================
    $roleKey = $roleKey ?? 'pyd';
    $status  = $evaluation->status ?? 'DRAFT';

    // lock status ikut flow awak
    $lockedPPP = !in_array($status, ['SUBMITTED','PPP_SCORED']);
    $lockedPPK = !in_array($status, ['PPP_SCORED','PPK_APPROVED']);
    $lockedPYD = !in_array($status, ['DRAFT']);

    $is_locked = match($roleKey) {
        'pyd' => $lockedPYD,
        'ppp' => $lockedPPP,
        'ppk' => $lockedPPK,
        default => true,
    };

    // items Bahagian III (sedia dihantar dari controller)
    $items  = $items ?? collect();
    $scores = $scores ?? collect();

    // wajaran ikut meta
    $weight = 50;

    // helper ambil score
    $getPPP = function($itemId) use ($scores) {
        $sc = $scores[$itemId] ?? null;
        return $sc?->ppp_score ?? null;
    };
    $getPPK = function($itemId) use ($scores) {
        $sc = $scores[$itemId] ?? null;
        return $sc?->ppk_score ?? null;
    };

    // kira total + weighted
    $countItems = $items->count();
    $maxPerItem = 10;
    $maxTotal   = max(1, $countItems * $maxPerItem);

    $pppTotal = 0;
    $ppkTotal = 0;

    foreach ($items as $it) {
        $v1 = $getPPP($it->id);
        $v2 = $getPPK($it->id);

        if ($v1 !== null && $v1 !== '') $pppTotal += (int)$v1;
        if ($v2 !== null && $v2 !== '') $ppkTotal += (int)$v2;
    }

    $pppWeighted = round(($pppTotal / $maxTotal) * $weight, 2);
    $ppkWeighted = round(($ppkTotal / $maxTotal) * $weight, 2);
@endphp

<div class="mb-6">
    <h4 class="fw-bold text-primary mb-3">
        BAHAGIAN III - PENGHASILAN KERJA <span class="text-muted">(Wajaran {{ $weight }}%)</span>
    </h4>

    <p class="text-muted mb-4" style="max-width: 980px;">
        Pegawai Penilai dikehendaki membuat penilaian berdasarkan pencapaian kerja sebenar PYD
        berbanding dengan SKT yang ditetapkan. Penilaian hendaklah berasaskan kepada penjelasan
        dan kriteria yang dinyatakan di bawah dengan menggunakan skala 1 hingga 10.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:50px" class="text-center">BIL</th>
                    <th>KRITERIA <div class="small text-muted">(Dinilai berasaskan SKT)</div></th>
                    <th style="width:120px" class="text-center">PPP</th>
                    <th style="width:120px" class="text-center">PPK</th>
                </tr>
            </thead>

            <tbody>
                @forelse($items as $idx => $it)
                    @php
                        // label: guna name / title (ikut model awak)
                        $title = $it->name ?? $it->title ?? 'Kriteria';
                        $desc  = $it->description ?? null;

                        $pppVal = old("ppp_scores.{$it->id}.score", $getPPP($it->id));
                        $ppkVal = old("ppk_scores.{$it->id}.score", $getPPK($it->id));

                        $canEditPPP = ($roleKey === 'ppp' && !$is_locked);
                        $canEditPPK = ($roleKey === 'ppk' && !$is_locked);
                    @endphp

                    <tr>
                        <td class="text-center fw-semibold">{{ $idx + 1 }}</td>

                        <td>
                            <div class="fw-bold text-uppercase">{{ $title }}</div>
                            @if($desc)
                                <div class="text-muted small mt-1">{{ $desc }}</div>
                            @endif
                        </td>

                        {{-- PPP box --}}
                        <td class="text-center">
                            <input
                                type="number"
                                min="0" max="10"
                                name="ppp_scores[{{ $it->id }}][score]"
                                value="{{ $pppVal }}"
                                class="form-control text-center"
                                style="width:80px; margin:0 auto; font-weight:700;"
                                {{ $canEditPPP ? '' : 'readonly' }}
                            >
                        </td>

                        {{-- PPK box --}}
                        <td class="text-center">
                            <input
                                type="number"
                                min="0" max="10"
                                name="ppk_scores[{{ $it->id }}][score]"
                                value="{{ $ppkVal }}"
                                class="form-control text-center"
                                style="width:80px; margin:0 auto; font-weight:700;"
                                {{ $canEditPPK ? '' : 'readonly' }}
                            >
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                            Tiada item untuk Bahagian III. Sila semak seeding / table competency items (code=III).
                        </td>
                    </tr>
                @endforelse
            </tbody>

            {{-- Jumlah markah ikut wajaran --}}
            <tfoot>
                <tr>
                    <td colspan="2" class="fw-bold">
                        Jumlah markah mengikut wajaran
                    </td>

                    <td class="text-center">
                        <div class="fw-bold">
                            {{ $pppWeighted }} <span class="text-muted">/ {{ $weight }}</span>
                        </div>
                        <div class="small text-muted">
                            {{ $pppTotal }} / {{ $maxTotal }} × {{ $weight }}
                        </div>
                    </td>

                    <td class="text-center">
                        <div class="fw-bold">
                            {{ $ppkWeighted }} <span class="text-muted">/ {{ $weight }}</span>
                        </div>
                        <div class="small text-muted">
                            {{ $ppkTotal }} / {{ $maxTotal }} × {{ $weight }}
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
