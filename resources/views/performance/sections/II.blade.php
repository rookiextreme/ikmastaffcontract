@php
    $data = $evaluation->bahagian_ii_data ?? [];

    $kegiatan     = $data['kegiatan'] ?? [[]];
    $latihanHadir = $data['latihan_hadir'] ?? [[]];
    $latihanPerlu = $data['latihan_perlu'] ?? [[]];

    /**
     * ✅ RULE EDIT
     * - PYD boleh edit hanya ketika DRAFT
     * - ADMIN boleh edit bila-bila (override)
     * - PPP/PPK read-only
     *
     * NOTE: partial ini TIDAK ada <form>.
     * Form disediakan di view masing-masing (PYD/Admin).
     */
    $readonly = !(
        ($roleKey === 'pyd' && ($evaluation->status ?? 'DRAFT') === 'DRAFT')
        || ($roleKey === 'admin')
    );
@endphp

<div class="mb-6">

    <h4 class="fw-bold text-primary mb-4">
        BAHAGIAN II – KEGIATAN DAN SUMBANGAN DI LUAR TUGAS RASMI / LATIHAN
    </h4>

    {{-- ===================== --}}
    {{-- 1. KEGIATAN --}}
    {{-- ===================== --}}
    <p class="fw-semibold">
        1. KEGIATAN DAN SUMBANGAN DI LUAR TUGAS RASMI
    </p>
    <p class="text-muted">
        Senaraikan kegiatan dan sumbangan di luar tugas rasmi seperti sukan / pertubuhan /
        sumbangan kreatif di peringkat Komuniti / Jabatan / Daerah / Negeri / Negara /
        Antarabangsa yang berfaedah kepada organisasi / komuniti / negara pada tahun yang dinilai.
    </p>

    <table class="table table-bordered align-middle">
        <thead class="bg-light">
        <tr>
            <th style="width:48%;">Senarai kegiatan / aktiviti / sumbangan</th>
            <th style="width:48%;">Peringkat kegiatan / aktiviti / sumbangan<br>
                <small>(nyatakan jawatan atau pencapaian)</small>
            </th>
            @if(!$readonly)
                <th style="width:4%;" class="text-center">Padam</th>
            @endif
        </tr>
        </thead>
        <tbody id="kegiatan-wrapper">
        @foreach($kegiatan as $i => $row)
            <tr>
                <td>
                    <textarea name="bahagian_ii[kegiatan][{{ $i }}][aktiviti]"
                              class="form-control"
                              rows="3"
                              {{ $readonly ? 'readonly' : '' }}>{{ $row['aktiviti'] ?? '' }}</textarea>
                </td>
                <td>
                    <textarea name="bahagian_ii[kegiatan][{{ $i }}][peringkat]"
                              class="form-control"
                              rows="3"
                              {{ $readonly ? 'readonly' : '' }}>{{ $row['peringkat'] ?? '' }}</textarea>
                </td>

                @if(!$readonly)
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-light-danger"
                                onclick="deleteRow(this, 'kegiatan-wrapper')"
                                title="Padam baris">
                            🗑
                        </button>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

    @if(!$readonly)
        <button type="button" class="btn btn-sm btn-light-primary mb-5"
                onclick="addRow('kegiatan-wrapper', kegiatanRow)">
            + Tambah Baris
        </button>
    @endif

    {{-- ===================== --}}
    {{-- 2. LATIHAN --}}
    {{-- ===================== --}}
    <p class="fw-semibold mt-6">2. LATIHAN</p>

    {{-- 2(i) --}}
    <p class="fw-semibold">(i) Senaraikan program latihan yang dihadiri</p>

    <table class="table table-bordered align-middle">
        <thead class="bg-light">
        <tr>
            <th>Nama Latihan<br><small>(nyatakan sijil jika ada)</small></th>
            <th>Tarikh / Tempoh</th>
            <th>Tempat</th>
            @if(!$readonly)
                <th style="width:4%;" class="text-center">Padam</th>
            @endif
        </tr>
        </thead>
        <tbody id="latihan-hadir-wrapper">
        @foreach($latihanHadir as $i => $row)
            <tr>
                <td>
                    <input type="text" class="form-control"
                           name="bahagian_ii[latihan_hadir][{{ $i }}][nama]"
                           value="{{ $row['nama'] ?? '' }}"
                           {{ $readonly ? 'readonly' : '' }}>
                </td>
                <td>
                    {{-- ✅ UBAH: tambah class js-date-range untuk calendar --}}
                    <input type="text"
                           class="form-control js-date-range"
                           name="bahagian_ii[latihan_hadir][{{ $i }}][tarikh]"
                           value="{{ $row['tarikh'] ?? '' }}"
                           placeholder="dd/mm/yyyy - dd/mm/yyyy"
                           autocomplete="off"
                           {{ $readonly ? 'readonly' : '' }}>
                </td>
                <td>
                    <input type="text" class="form-control"
                           name="bahagian_ii[latihan_hadir][{{ $i }}][tempat]"
                           value="{{ $row['tempat'] ?? '' }}"
                           {{ $readonly ? 'readonly' : '' }}>
                </td>

                @if(!$readonly)
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-light-danger"
                                onclick="deleteRow(this, 'latihan-hadir-wrapper')"
                                title="Padam baris">
                            🗑
                        </button>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

    @if(!$readonly)
        <button type="button" class="btn btn-sm btn-light-primary mb-5"
                onclick="addRow('latihan-hadir-wrapper', latihanHadirRow)">
            + Tambah Baris
        </button>
    @endif

    {{-- 2(ii) --}}
    <p class="fw-semibold">(ii) Senaraikan latihan yang diperlukan</p>

    <table class="table table-bordered align-middle">
        <thead class="bg-light">
        <tr>
            <th>Nama / Bidang Latihan</th>
            <th>Sebab Diperlukan</th>
            @if(!$readonly)
                <th style="width:4%;" class="text-center">Padam</th>
            @endif
        </tr>
        </thead>
        <tbody id="latihan-perlu-wrapper">
        @foreach($latihanPerlu as $i => $row)
            <tr>
                <td>
                    <input type="text" class="form-control"
                           name="bahagian_ii[latihan_perlu][{{ $i }}][bidang]"
                           value="{{ $row['bidang'] ?? '' }}"
                           {{ $readonly ? 'readonly' : '' }}>
                </td>
                <td>
                    <input type="text" class="form-control"
                           name="bahagian_ii[latihan_perlu][{{ $i }}][sebab]"
                           value="{{ $row['sebab'] ?? '' }}"
                           {{ $readonly ? 'readonly' : '' }}>
                </td>

                @if(!$readonly)
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-light-danger"
                                onclick="deleteRow(this, 'latihan-perlu-wrapper')"
                                title="Padam baris">
                            🗑
                        </button>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>

    @if(!$readonly)
        <button type="button" class="btn btn-sm btn-light-primary"
                onclick="addRow('latihan-perlu-wrapper', latihanPerluRow)">
            + Tambah Baris
        </button>
    @endif

</div>

{{-- ===================== --}}
{{-- JS Helper (Tambah/Padam) --}}
{{-- ===================== --}}
<script>
function addRow(wrapperId, rowHtml) {
    document.getElementById(wrapperId).insertAdjacentHTML('beforeend', rowHtml());

    // ✅ TAMBAH: init calendar untuk row baru
    if (typeof window.initBahagianIIPickers === 'function') {
        window.initBahagianIIPickers();
    }
}

function deleteRow(btn, wrapperId) {
    const tbody = document.getElementById(wrapperId);
    const rows = tbody.querySelectorAll('tr');

    // pastikan sekurang-kurangnya 1 row kekal
    if (rows.length <= 1) {
        const last = rows[0];
        last.querySelectorAll('input, textarea').forEach(el => el.value = '');
        return;
    }

    btn.closest('tr').remove();
}

// ========= TEMPLATE ROWS =========
function kegiatanRow() {
    let i = document.querySelectorAll('#kegiatan-wrapper tr').length;
    return `
    <tr>
        <td>
            <textarea class="form-control" rows="3" name="bahagian_ii[kegiatan][${i}][aktiviti]"></textarea>
        </td>
        <td>
            <textarea class="form-control" rows="3" name="bahagian_ii[kegiatan][${i}][peringkat]"></textarea>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteRow(this, 'kegiatan-wrapper')" title="Padam baris">🗑</button>
        </td>
    </tr>`;
}

function latihanHadirRow() {
    let i = document.querySelectorAll('#latihan-hadir-wrapper tr').length;
    return `
    <tr>
        <td><input class="form-control" name="bahagian_ii[latihan_hadir][${i}][nama]"></td>

        {{-- ✅ UBAH: tambah class js-date-range + placeholder --}}
        <td>
            <input class="form-control js-date-range"
                   name="bahagian_ii[latihan_hadir][${i}][tarikh]"
                   placeholder="dd/mm/yyyy - dd/mm/yyyy"
                   autocomplete="off">
        </td>

        <td><input class="form-control" name="bahagian_ii[latihan_hadir][${i}][tempat]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteRow(this, 'latihan-hadir-wrapper')" title="Padam baris">🗑</button>
        </td>
    </tr>`;
}

function latihanPerluRow() {
    let i = document.querySelectorAll('#latihan-perlu-wrapper tr').length;
    return `
    <tr>
        <td><input class="form-control" name="bahagian_ii[latihan_perlu][${i}][bidang]"></td>
        <td><input class="form-control" name="bahagian_ii[latihan_perlu][${i}][sebab]"></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light-danger" onclick="deleteRow(this, 'latihan-perlu-wrapper')" title="Padam baris">🗑</button>
        </td>
    </tr>`;
}
</script>

{{-- ===================== --}}
{{-- ✅ TAMBAH: Init Calendar Picker (Flatpickr) --}}
{{-- ===================== --}}
@if(!$readonly)
<script>
(function(){
    // ✅ Pastikan flatpickr wujud (load dari layout master)
    function init(){
        if (typeof flatpickr === 'undefined') return;

        document.querySelectorAll('.js-date-range').forEach(function(el){
            if (el._flatpickr) return;

            flatpickr(el, {
    mode: "range",
    dateFormat: "d/m/Y",
    allowInput: true,

    locale: {
        rangeSeparator: " hingga "
    }
});
        });
    }

    // expose supaya addRow() boleh panggil
    window.initBahagianIIPickers = init;

    document.addEventListener('DOMContentLoaded', init);
})();
</script>
@endif