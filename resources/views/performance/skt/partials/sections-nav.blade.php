@php
    $bahagian = strtoupper((string)($bahagian ?? 'I'));
    $roleKey  = $roleKey ?? 'pyd';
    $status   = $status ?? ($evaluation->status ?? 'DRAFT');

    $tabs = [
        'I'   => 'Bahagian I',
        'II'  => 'Bahagian II',
        'III' => 'Bahagian III',
    ];

    // ✅ WAJIB ikut role
    $requiredByRole = [
        'pyd' => ['I','II','III'],
        'ppp' => ['III'],
    ];
    $requiredSections = $requiredByRole[$roleKey] ?? [];
    $isRequired = function(string $code) use ($requiredSections) {
        return in_array($code, $requiredSections, true);
    };

    // ========= check lengkap I (✓) =========
    $e = $evaluation;

    $i = is_array($e->skt_bahagian_i ?? null) ? $e->skt_bahagian_i : [];
    $iItems = (array)($i['items'] ?? []);

    // filter row kosong
    $iItems = array_values(array_filter($iItems, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return !($a === '' && $p === '');
    }));

    $filledI = 0; $okI = true;
    foreach($iItems as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        if($a==='' && $p==='') continue;
        $filledI++;
        if($a==='' || $p===''){ $okI=false; break; }
    }
    $iComplete = ($filledI > 0) && $okI;

    // ========= check lengkap II (✓) =========
    $ii = is_array($e->skt_bahagian_ii ?? null) ? $e->skt_bahagian_ii : [];
    $tambah = (array)($ii['tambah'] ?? []);
    $gugur  = (array)($ii['gugur'] ?? []);

    // filter row kosong
    $tambah = array_values(array_filter($tambah, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return !($a === '' && $p === '');
    }));
    $gugur = array_values(array_filter($gugur, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        return $a !== '';
    }));

    $filledTambah = 0; $okTambah = true;
    foreach($tambah as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        if($a==='' && $p==='') continue;
        $filledTambah++;
        if($a==='' || $p===''){ $okTambah=false; break; }
    }

    $filledGugur = 0;
    foreach($gugur as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        if($a==='') continue;
        $filledGugur++;
    }

    // ✅ BETUL: Gugur boleh kosong (ikut II.blade.php)
    // complete jika ada sekurang-kurangnya 1 item (Ditambah atau Digugurkan)
    // dan jika Ditambah ada isi, aktiviti & petunjuk mesti lengkap
    $iiComplete = (($filledTambah + $filledGugur) > 0) && $okTambah;

    // ========= check lengkap III (✓) ikut role =========
    $dataIII = (array)($e->skt_bahagian_iii ?? []);
    $ulasanPYD = trim((string)($dataIII['ulasan_pyd'] ?? ''));
    $ulasanPPP = trim((string)($dataIII['ulasan_ppp'] ?? ''));

    // (ikut repo: min 3 aksara untuk PYD, PPP sekurang-kurangnya ada isi)
    $iiiComplete = ($roleKey === 'ppp')
        ? ($ulasanPPP !== '')
        : (mb_strlen($ulasanPYD) >= 3);
@endphp

{{-- ✅ WRAPPER PUTIH (macam Bahagian II) --}}
<div class="card border mb-4 bg-white">
    <div class="card-body py-3">

        <ul class="nav nav-tabs mb-3">
            @foreach($tabs as $code => $label)
                @php $href = $baseUrl.'?bahagian='.$code; @endphp

                <li class="nav-item">
                    <a class="nav-link {{ $bahagian === $code ? 'active' : '' }}" href="{{ $href }}">
                        {{ $label }}

                        {{-- ✅ STAR MERAH (wajib ikut role) --}}
                        @if($isRequired($code))
                            <span class="text-danger fw-bold ms-1">*</span>
                        @endif

                        {{-- ✅ ✓ ikut bahagian --}}
                        @if($code==='I' && $iComplete) <span class="ms-1 text-success">✓</span> @endif
                        @if($code==='II' && $iiComplete) <span class="ms-1 text-success">✓</span> @endif
                        @if($code==='III' && $iiiComplete) <span class="ms-1 text-success">✓</span> @endif
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="text-muted small">
            * Semua bahagian boleh dibuka. Untuk <strong>Hantar / Sahkan</strong>, sistem akan semak kelengkapan Bahagian I & II.
        </div>

    </div>
</div>