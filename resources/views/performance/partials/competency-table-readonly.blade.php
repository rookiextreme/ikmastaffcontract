{{-- resources/views/performance/partials/competency-table-readonly.blade.php --}}

@php
    $bahagian = strtoupper(trim($bahagian ?? ($code ?? request('bahagian', 'III'))));

    $items  = $items ?? collect();
    $scores = $scores ?? collect(); // keyBy(competency_item_id)

    $sectionMeta = $sectionMeta ?? [];
    $meta   = $sectionMeta[$bahagian] ?? null;
    $weight = (int)($meta['weight'] ?? 0);
    $pydGroup = strtoupper(
    trim((string) ($evaluation->assignment->pyd_group ?? 'BC'))
);

    // =========================
    // NORMALIZE NAME (supaya mapping match DB)
    // (lebih robust: & => DAN, buang simbol, kemas space)
    // =========================
    $norm = function ($v) {
        $v = (string)$v;
        $v = str_replace(["\xE2\x80\x98","\xE2\x80\x99","\xE2\x80\x9C","\xE2\x80\x9D"], ["'","'","\"","\""], $v);
        $v = mb_strtoupper($v);

        $v = str_replace('&', ' DAN ', $v);
        $v = str_replace(['-', '–', '—'], ' ', $v);
        $v = preg_replace('/[^A-Z0-9\s]/u', ' ', $v);
        $v = preg_replace('/\s+/', ' ', $v);
        return trim($v);
    };

    // =========================
    // DETAIL MAPPING BAHAGIAN III (Wajaran 50%)
    // =========================
    $mapIII = [
        $norm('Kuantiti Hasil Kerja') => [
            'bil' => '1.',
            'title' => 'KUANTITI HASIL KERJA -',
            'desc' => 'Kuantiti hasil kerja seperti jumlah, bilangan, kadar, kekerapan dan sebagainya berbanding dengan sasaran kuantiti kerja yang ditetapkan.',
        ],
        $norm('Kualiti Hasil Kerja (Kesempurnaan, teratur & kemas)') => [
            'bil' => '2.',
            'title' => 'KUALITI HASIL KERJA -',
            'desc' => '2.1. Dinilai dari segi kesempurnaan, teratur dan kemas.',
        ],
        $norm('Kualiti Hasil Kerja (Usaha & inisiatif untuk mencapai kesempurnaan)') => [
    'bil'   => '',
    'title' => '',
    'desc'  => '2.2. Dinilai dari segi usaha dan inisiatif untuk mencapai kesempurnaan hasil kerja.',
],
        $norm('Ketepatan Masa') => [
            'bil' => '3.',
            'title' => 'KETEPATAN MASA -',
            'desc' => 'Kebolehan menghasilkan kerja atau melaksanakan tugas dalam tempoh masa yang ditetapkan.',
        ],
        $norm('Keberkesanan Hasil Kerja') => [
            'bil' => '4.',
            'title' => 'KEBERKESANAN HASIL KERJA -',
            'desc' => 'Dinilai dari segi memenuhi kehendak “stake-holder” atau pelanggan.',
        ],
    ];

    // =========================
    // DETAIL MAPPING BAHAGIAN IV (Wajaran 25%)
    // =========================
    $mapIV = [
        $norm('Ilmu Pengetahuan & Kemahiran dalam Bidang Kerja') => [
            'bil' => '1.',
            'title' => 'ILMU PENGETAHUAN & KEMAHIRAN DALAM BIDANG KERJA -',
            'desc' => 'Mempunyai ilmu pengetahuan dan kemahiran/ kepakaran dalam menghasilkan kerja meliputi kebolehan mengaplikasi, menganalisis serta menyelesaikan masalah.',
        ],
        $norm('Pelaksanaan Dasar, Peraturan & Arahan Pentadbiran') => [
            'bil' => '2.',
            'title' => 'PELAKSANAAN DASAR, PERATURAN DAN ARAHAN PENTADBIRAN -',
            'desc' => 'Kebolehan menghayati dan melaksanakan dasar, peraturan dan arahan pentadbiran berkaitan dengan bidang tugasnya.',
        ],
        $norm('Keberkesanan Komunikasi') => [
            'bil' => '3.',
            'title' => 'KEBERKESANAN KOMUNIKASI -',
            'desc' => 'Kebolehan menyampaikan maksud, pendapat, kefahaman atau arahan secara lisan dan tulisan berkaitan dengan bidang tugas serta merangkumi penggunaan bahasa melalui tulisan dan lisan dengan menggunakan tatabahasa dan persembahan yang baik.',
        ],
    ];

    // =========================
    // DETAIL MAPPING BAHAGIAN V (Wajaran 20%) - ikut borang + ikut nama DB Irham
    // =========================
    $mapV = [
    $norm('Ciri-ciri Pemimpin') => [
        'bil'   => '1.',
        'title' => 'CIRI-CIRI PEMIMPIN',
        'desc'  => 'Mempunyai wawasan, komitmen, kebolehan membuat keputusan, menggerak dan memberi dorongan kepada pegawai ke arah pencapaian objektif organisasi.',
    ],

    $norm('Kebolehan mengelola') => [
        'bil'   => $pydGroup === 'A' ? '2.' : '1.',
        'title' => 'KEBOLEHAN MENGELOLA -',
        'desc'  => 'Keupayaan dan kebolehan menggembleng segala sumber dalam kawalannya seperti kewangan, tenaga manusia, peralatan dan maklumat bagi merancang, mengatur, membahagi dan mengendalikan sesuatu tugas untuk mencapai objektif organisasi.',
    ],

    $norm('Disiplin') => [
        'bil'   => $pydGroup === 'A' ? '3.' : '2.',
        'title' => 'DISIPLIN -',
        'desc'  => 'Mempunyai daya kawal diri dari segi mental dan fizikal termasuk mematuhi peraturan, menepati masa, menunaikan janji dan bersifat sabar.',
    ],

    $norm('Proaktif & inovatif') => [
        'bil'   => $pydGroup === 'A' ? '4.' : '3.',
        'title' => 'PROAKTIF & INOVATIF',
        'desc'  => 'Kebolehan menjangka kemungkinan, mencipta dan mengeluarkan idea baru serta membuat pembaharuan bagi mempertingkatkan kualiti dan produktiviti organisasi.',
    ],

    $norm('Jalinan hubungan & kerjasama') => [
        'bil'   => $pydGroup === 'A' ? '5.' : '4.',
        'title' => 'JALINAN HUBUNGAN & KERJASAMA',
        'desc'  => 'Kebolehan pegawai dalam mewujudkan suasana kerjasama yang harmoni dan mesra serta boleh menyesuaikan diri dalam semua keadaan.',
    ],
];

    // =========================
    // DETAIL MAPPING BAHAGIAN VI (Wajaran 5%)
    // ✅ Irham nak tinggal ayat ini sahaja (tiada bil, tiada tajuk)
    // =========================
    $mapVI = [
        $norm('Kegiatan & sumbangan di luar tugas rasmi') => [
            'desc' => 'Peringkat: Komuniti / Jabatan / Daerah / Negeri / Negara / Antarabangsa.',
        ],
    ];

    $getDetail = function ($itemName) use ($bahagian, $mapIII, $mapIV, $mapV, $mapVI, $norm) {
        $k = $norm($itemName);

        if ($bahagian === 'III') return $mapIII[$k] ?? null;
        if ($bahagian === 'IV')  return $mapIV[$k] ?? null;
        if ($bahagian === 'V')   return $mapV[$k] ?? null;
        if ($bahagian === 'VI')  return $mapVI[$k] ?? null;

        return null;
    };

    // =========================
    // MAX SCORE (ikut borang)
    // =========================
    $maxScore = match ($bahagian) {
    'III' => 50,
    'IV'  => 30,
    'V'   => $pydGroup === 'A' ? 50 : 40,
    'VI'  => 10,
    default => max(1, (int) $items->count() * 10),
};

    // jumlah markah
    $sumPPP = 0;
    $sumPPK = 0;

    foreach ($items as $it) {
        $row = $scores->get($it->id);

        // BAHAGIAN VI: markah boleh kosong (null). Jadi treat null sebagai 0 untuk kiraan wajaran.
        $sumPPP += (int)($row->ppp_score ?? 0);
        $sumPPK += (int)($row->ppk_score ?? 0);
    }

    $calcWeighted = function ($sum, $max, $w) {
        if ($max <= 0 || $w <= 0) return 0;
        return ($sum / $max) * $w;
    };

    $weightedPPP = $calcWeighted($sumPPP, $maxScore, $weight);
    $weightedPPK = $calcWeighted($sumPPK, $maxScore, $weight);

    $fmt = function ($v) {
        return rtrim(rtrim(number_format((float)$v, 1, '.', ''), '0'), '.');
    };
@endphp

<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            @if($bahagian !== 'VI')
                <th style="width:70px">BIL</th>
            @endif

            <th>KRITERIA<br><small class="text-muted">(Dinilai berasaskan SKT)</small></th>
            <th class="text-center" style="width:140px">PPP</th>
            <th class="text-center" style="width:140px">PPK</th>
        </tr>
    </thead>

    <tbody>
    @foreach($items as $idx => $item)
        @php
            $row = $scores->get($item->id);
            $detail = $getDetail($item->name);

            // =========================
            // BIL (BUANG untuk VI)
            // =========================
            $bilText = '';

if ($bahagian !== 'VI') {
    $bilText = ($idx + 1).'.';

    /*
     * Gunakan nilai mapping walaupun sengaja dikosongkan.
     * Contoh Bahagian III item 2.2 tidak mempunyai nombor utama.
     */
    if ($detail && array_key_exists('bil', $detail)) {
        $bilText = $detail['bil'];
    }
}

            // =========================
            // TITLE/DESC
            // ✅ VI: buang tajuk & buang fallback
            // =========================
            if ($bahagian === 'VI') {
                $titleText = '';
                $descText  = $detail['desc'] ?? '';
            } else {
                $titleText = $detail['title'] ?? $item->name;
                $descText  = $detail['desc'] ?? '';

                // fallback: kalau mapping tiada desc, guna item->description
                if ($descText === '' && !empty($item->description)) {
                    $descText = $item->description;
                }
            }
        @endphp

        <tr>
            @if($bahagian !== 'VI')
                <td class="text-center fw-semibold">{{ $bilText }}</td>
            @endif

            <td>
                @if(!empty($titleText))
    <div class="fw-bold text-uppercase">
        {{ $titleText }}
    </div>
@endif

@if(!empty($descText))
    <div class="text-muted small {{ !empty($titleText) ? 'mt-1' : '' }}"
         style="white-space: pre-line;">
        {{ $descText }}
    </div>
@endif
            </td>

            <td class="text-center">
                <span class="badge bg-light text-dark border">
                    {{ $row->ppp_score ?? '-' }}
                </span>
            </td>

            <td class="text-center">
                <span class="badge bg-light text-dark border">
                    {{ $row->ppk_score ?? '-' }}
                </span>
            </td>
        </tr>
    @endforeach

    {{-- JUMLAH MARKAH MENGIKUT WAJARAN --}}
    @if($weight > 0)
        <tr class="table-light">
            <td colspan="{{ $bahagian === 'VI' ? 1 : 2 }}" class="fw-semibold">
                Jumlah markah mengikut wajaran
            </td>

            <td class="text-center">
                <div class="fw-bold">
                    {{ $sumPPP }} / {{ $maxScore }}
                </div>
                <div class="text-muted small">
                    {{ $sumPPP }} / {{ $maxScore }} x {{ $weight }} = {{ $fmt($weightedPPP) }}
                </div>
            </td>

            <td class="text-center">
                <div class="fw-bold">
                    {{ $sumPPK }} / {{ $maxScore }}
                </div>
                <div class="text-muted small">
                    {{ $sumPPK }} / {{ $maxScore }} x {{ $weight }} = {{ $fmt($weightedPPK) }}
                </div>
            </td>
        </tr>
    @endif
    </tbody>
</table>
