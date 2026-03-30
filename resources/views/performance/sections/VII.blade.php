{{-- 3) Pengesahan --}}
<div class="mb-4">
    <div class="fw-semibold mb-3">
        3. Adalah disahkan bahawa prestasi pegawai ini telah dimaklumkan kepada PYD.
    </div>

    <div class="border p-4 rounded" style="max-width: 620px;">
        <div class="row mb-2">
            <div class="col-4 text-muted">Nama PPP</div>
            <div class="col-8 fw-semibold">
                {{ $pppUser->name ?? '-' }}
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4 text-muted">Jawatan</div>
            <div class="col-8 fw-semibold">
                {{ $pppUser->jawatan ?? '-' }}
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4 text-muted">Kementerian / Jabatan</div>
            <div class="col-8 fw-semibold">
                {{ $pppUser->kementerian_jabatan ?? '-' }}
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-4 text-muted">Tarikh Pengesahan</div>
            <div class="col-8 fw-semibold">
                {{ $evaluation->ppp_signed_at
                    ? \Carbon\Carbon::parse($evaluation->ppp_signed_at)->format('d F Y')
                    : '-' }}
            </div>
        </div>

        <div class="row">
            <div class="col-4 text-muted">Status</div>
            <div class="col-8 fw-bold text-success">
                {{ $evaluation->status === 'PPP_SCORED' ? 'DISAHKAN OLEH PPP' : 'BELUM DISAHKAN' }}
            </div>
        </div>
    </div>
</div>
