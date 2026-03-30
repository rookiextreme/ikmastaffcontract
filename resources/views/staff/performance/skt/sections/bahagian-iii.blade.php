@php
    $e = $evaluation;
    $data = (array)($e->skt_bahagian_iii ?? []);
@endphp

<form method="POST" action="{{ route('staff.performance.skt.save') }}">
    @csrf
    <input type="hidden" name="bahagian" value="III">

    <div class="card border mb-6">
        <div class="card-header">
            <h4 class="card-title mb-0">BAHAGIAN III - Laporan dan Ulasan Keseluruhan Pencapaian Sasaran Kerja Tahunan</h4>
        </div>

        <div class="card-body">
            <div class="mb-4">
                <label class="form-label fw-semibold">1. Laporan / Ulasan Oleh PYD</label>
                <textarea class="form-control" rows="5" name="skt_bahagian_iii[ulasan_pyd]">{{ $data['ulasan_pyd'] ?? '' }}</textarea>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">2. Laporan / Ulasan oleh PPP</label>
                <textarea class="form-control" rows="5" disabled>{{ $data['ulasan_ppp'] ?? '' }}</textarea>
                <div class="text-muted small mt-1">* Ruangan ini akan diisi oleh PPP.</div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </div>
</form>