@php
    $e = $evaluation;
    $data = (array)($e->skt_bahagian_ii ?? []);
    $tambah = (array)($data['tambah'] ?? []);
    $gugur  = (array)($data['gugur'] ?? []);

    $readonly = in_array($roleKey, ['ppp','ppk','admin'], true) || $is_locked;
    $canEdit  = ($roleKey === 'pyd') && !$readonly;

    // ✅ buang row kosong
    $tambah = array_values(array_filter($tambah, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return !($a === '' && $p === '');
    }));

    $gugur = array_values(array_filter($gugur, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        return !($a === '');
    }));

    // ✅ minimum 1 row bila tiada data (untuk PYD edit mode)
    if(count($tambah) < 1){
        $tambah = [['aktiviti'=>'','petunjuk'=>'']];
    }
    if(count($gugur) < 1){
        $gugur = [['aktiviti'=>'']];
    }

    // ✅ check lengkap II (KEKAL LOGIC ASAL)
    $filledTambah = 0; $okTambah = true;
    foreach($tambah as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        if($a === '' && $p === '') continue;
        $filledTambah++;
        if($a === '' || $p === ''){ $okTambah = false; break; }
    }

    $filledGugur = 0;
    foreach($gugur as $r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        if($a === '') continue;
        $filledGugur++;
    }

    $iiComplete = (($filledTambah + $filledGugur) > 0) && $okTambah;

    // paparan read-only untuk admin / ppp
    $tambahDisplay = array_values(array_filter($tambah, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        $p = trim((string)($r['petunjuk'] ?? ''));
        return $a !== '' || $p !== '';
    }));

    $gugurDisplay = array_values(array_filter($gugur, function($r){
        $a = trim((string)($r['aktiviti'] ?? ''));
        return $a !== '';
    }));
@endphp

<style>
    .skt-readonly-box {
        min-height: 70px;
        background: #f8f9fb;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 14px;
        color: #3f4254;
        line-height: 1.65;
        white-space: pre-wrap;
    }

    .skt-section-note {
        font-size: 0.85rem;
        color: #7e8299;
        margin-top: 6px;
    }

    .skt-empty-box {
        background: #f8f9fb;
        border: 1px dashed #d6dae1;
        border-radius: 10px;
        padding: 14px;
        color: #7e8299;
        font-style: italic;
    }
</style>

@if(in_array($roleKey, ['admin','ppp','ppk'], true))
    <div class="card border mb-6">
        <div class="card-header">
            <h4 class="card-title mb-0">BAHAGIAN II - Kajian Semula Sasaran Kerja Tahunan Pertengahan Tahun</h4>
        </div>

        <div class="card-body">

            {{-- ========================= --}}
            {{-- 1. Aktiviti Ditambah --}}
            {{-- ========================= --}}
            <h5 class="mb-3">1. Aktiviti / Projek Yang Ditambah</h5>
            <div class="fst-italic small mb-3">
                (PYD hendaklah menyenaraikan aktiviti / projek yang ditambah berserta
                petunjuk prestasinya setelah berbincang dengan PPP.)
            </div>

            @if(count($tambahDisplay) > 0)
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
                            @foreach($tambahDisplay as $i => $row)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="skt-readonly-box">{!! nl2br(e(trim((string)($row['aktiviti'] ?? '')) !== '' ? $row['aktiviti'] : '-')) !!}</div>
                                    </td>
                                    <td>
                                        <div class="skt-readonly-box">{!! nl2br(e(trim((string)($row['petunjuk'] ?? '')) !== '' ? $row['petunjuk'] : '-')) !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="skt-empty-box mb-5">
                    Tiada aktiviti / projek ditambah direkodkan.
                </div>
            @endif

            {{-- ========================= --}}
            {{-- 2. Aktiviti Digugurkan --}}
            {{-- ========================= --}}
            <h5 class="mb-3">2. Aktiviti / Projek Yang Digugurkan</h5>
            <div class="fst-italic small mb-3">
                (PYD hendaklah menyenaraikan aktiviti / projek yang digugurkan
                setelah berbincang dengan PPP.)
            </div>

            @if(count($gugurDisplay) > 0)
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">Bil.</th>
                                <th>Aktiviti / Projek</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gugurDisplay as $i => $row)
                                <tr>
                                    <td class="text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="skt-readonly-box">{!! nl2br(e(trim((string)($row['aktiviti'] ?? '')) !== '' ? $row['aktiviti'] : '-')) !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="skt-empty-box">
                    Tiada aktiviti / projek digugurkan direkodkan.
                </div>
            @endif

            <div class="skt-section-note">
                * Paparan ini adalah read-only untuk {{ $roleKey === 'admin' ? 'Admin' : ($roleKey === 'ppk' ? 'PPK' : 'PPP') }}.
            </div>

        </div>
    </div>
@else
    <form method="POST" action="{{ route('staff.performance.skt.save') }}">
        @csrf
        <input type="hidden" name="bahagian" value="II">

        <div class="card border mb-6">
            <div class="card-header">
                <h4 class="card-title mb-0">BAHAGIAN II - Kajian Semula Sasaran Kerja Tahunan Pertengahan Tahun</h4>
            </div>

            <div class="card-body">

                {{-- ========================= --}}
                {{-- 1. Aktiviti Ditambah --}}
                {{-- ========================= --}}
                <h5 class="mb-3">1. Aktiviti / Projek Yang Ditambah</h5>
                <div class="fst-italic small mb-3">
                    (PYD hendaklah menyenaraikan aktiviti / projek yang ditambah berserta
                    petunjuk prestasinya setelah berbincang dengan PPP.)
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">Bil.</th>
                                <th>Ringkasan Aktiviti / Projek</th>
                                <th>Petunjuk Prestasi</th>
                                @if($canEdit)
                                    <th style="width:70px;" class="text-center">Padam</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody id="skt-ii-tambah-wrapper">
                            @foreach($tambah as $i => $row)
                                <tr class="skt-ii-tambah-row">
                                    <td class="text-center skt-bil">{{ $i+1 }}</td>
                                    <td>
                                        <textarea class="form-control" rows="2"
                                                  name="skt_bahagian_ii[tambah][{{ $i }}][aktiviti]"
                                                  {{ $readonly ? 'readonly' : '' }}>{{ $row['aktiviti'] ?? '' }}</textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" rows="2"
                                                  name="skt_bahagian_ii[tambah][{{ $i }}][petunjuk]"
                                                  {{ $readonly ? 'readonly' : '' }}>{{ $row['petunjuk'] ?? '' }}</textarea>
                                    </td>

                                    @if($canEdit)
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-light-danger"
                                                    onclick="deleteRowSKTII(this, 'skt-ii-tambah-wrapper', 'skt-ii-tambah-row')"
                                                    title="Padam baris">🗑</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($canEdit)
                    <button type="button" class="btn btn-sm btn-light-primary mb-5"
                            onclick="addRowSKTII('skt-ii-tambah-wrapper', sktIITambahRow)">
                        + Tambah Baris
                    </button>
                @endif

                {{-- ========================= --}}
                {{-- 2. Aktiviti Digugurkan --}}
                {{-- ========================= --}}
                <h5 class="mb-3">2. Aktiviti / Projek Yang Digugurkan</h5>
                <div class="fst-italic small mb-3">
                    (PYD hendaklah menyenaraikan aktiviti / projek yang digugurkan
                    setelah berbincang dengan PPP.)
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">Bil.</th>
                                <th>Aktiviti / Projek</th>
                                @if($canEdit)
                                    <th style="width:70px;" class="text-center">Padam</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody id="skt-ii-gugur-wrapper">
                            @foreach($gugur as $i => $row)
                                <tr class="skt-ii-gugur-row">
                                    <td class="text-center skt-bil">{{ $i+1 }}</td>
                                    <td>
                                        <textarea class="form-control" rows="2"
                                                  name="skt_bahagian_ii[gugur][{{ $i }}][aktiviti]"
                                                  {{ $readonly ? 'readonly' : '' }}>{{ $row['aktiviti'] ?? '' }}</textarea>
                                    </td>

                                    @if($canEdit)
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-sm btn-light-danger"
                                                    onclick="deleteRowSKTII(this, 'skt-ii-gugur-wrapper', 'skt-ii-gugur-row')"
                                                    title="Padam baris">🗑</button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($canEdit)
                    <button type="button" class="btn btn-sm btn-light-primary mb-4"
                            onclick="addRowSKTII('skt-ii-gugur-wrapper', sktIIGugurRow)">
                        + Tambah Baris
                    </button>
                @endif

                {{-- ✅ Simpan (PYD sahaja) --}}
                @if($roleKey === 'pyd')
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary" {{ $is_locked ? 'disabled' : '' }}>Simpan</button>
                    </div>
                @endif

                {{-- ✅ mesej lengkap --}}
                @if(!$iiComplete)
                    <div class="text-muted small mt-3">
                        * Lengkapkan sekurang-kurangnya 1 item (Ditambah atau Digugurkan).
                        Jika “Ditambah”, aktiviti & petunjuk mesti penuh.
                    </div>
                @endif

            </div>
        </div>
    </form>
@endif

{{-- ===================== --}}
{{-- ✅ Inline JS (Tambah/Padam + Reindex) --}}
{{-- ===================== --}}
@if($canEdit)
<script>
function addRowSKTII(wrapperId, rowHtml) {
    document.getElementById(wrapperId).insertAdjacentHTML('beforeend', rowHtml());
    reindexSKTII(wrapperId);
}

function deleteRowSKTII(btn, wrapperId, rowClass) {
    const tbody = document.getElementById(wrapperId);
    const rows = tbody.querySelectorAll('tr.' + rowClass);

    if (rows.length <= 1) {
        rows[0].querySelectorAll('textarea, input').forEach(el => el.value = '');
        return;
    }

    btn.closest('tr').remove();
    reindexSKTII(wrapperId);
}

function reindexSKTII(wrapperId) {
    const tbody = document.getElementById(wrapperId);
    const rows = tbody.querySelectorAll('tr');

    rows.forEach((tr, idx) => {
        const bil = tr.querySelector('.skt-bil');
        if (bil) bil.textContent = idx + 1;

        tr.querySelectorAll('textarea[name^="skt_bahagian_ii[tambah]"]').forEach(el => {
            el.name = el.name.replace(/skt_bahagian_ii\[tambah]\[\d+]/, `skt_bahagian_ii[tambah][${idx}]`);
        });

        tr.querySelectorAll('textarea[name^="skt_bahagian_ii[gugur]"]').forEach(el => {
            el.name = el.name.replace(/skt_bahagian_ii\[gugur]\[\d+]/, `skt_bahagian_ii[gugur][${idx}]`);
        });
    });
}

function sktIITambahRow() {
    return `
    <tr class="skt-ii-tambah-row">
        <td class="text-center skt-bil">1</td>
        <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[tambah][0][aktiviti]"></textarea></td>
        <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[tambah][0][petunjuk]"></textarea></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light-danger"
                onclick="deleteRowSKTII(this, 'skt-ii-tambah-wrapper', 'skt-ii-tambah-row')" title="Padam baris">🗑</button>
        </td>
    </tr>`;
}

function sktIIGugurRow() {
    return `
    <tr class="skt-ii-gugur-row">
        <td class="text-center skt-bil">1</td>
        <td><textarea class="form-control" rows="2" name="skt_bahagian_ii[gugur][0][aktiviti]"></textarea></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light-danger"
                onclick="deleteRowSKTII(this, 'skt-ii-gugur-wrapper', 'skt-ii-gugur-row')" title="Padam baris">🗑</button>
        </td>
    </tr>`;
}

document.addEventListener('DOMContentLoaded', () => {
    reindexSKTII('skt-ii-tambah-wrapper');
    reindexSKTII('skt-ii-gugur-wrapper');
});
</script>
@endif