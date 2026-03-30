@php
  $roleKey = $roleKey ?? 'ppk';
  $canApprove = ($roleKey === 'ppk' && ($evaluation->status ?? '') === 'PPP_SCORED');
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0">Bahagian IX: Pengesahan PPK</h4>
    </div>

    <div class="card-body">

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="alert alert-success">
            <div class="fw-bold mb-1">Sahkan & Tamatkan Penilaian</div>
            <div class="small text-muted">Selepas disahkan, status akan menjadi <strong>PPK_APPROVED</strong>.</div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <label class="form-label">Total (PPP)</label>
                <input class="form-control" value="{{ $evaluation->ppp_total_score ?? '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Total Akhir (PPK)</label>
                <input class="form-control" value="{{ $evaluation->ppk_total_score ?? '-' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tarikh Sah</label>
                <input class="form-control" value="{{ $evaluation?->ppk_approved_at?->format('d/m/Y') ?? '-' }}" disabled>
            </div>
        </div>

        @if($roleKey === 'ppk')
            <form method="POST" action="{{ route('ppk.performance.approve', $evaluation->id) }}">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Catatan Akhir PPK <span class="text-danger">*</span></label>
                    <textarea name="ppk_comment" class="form-control" rows="4" {{ $canApprove ? '' : 'disabled' }}>{{ old('ppk_comment', $evaluation->ppk_comment) }}</textarea>
                    <div class="text-muted small mt-1">Min 3 aksara.</div>
                </div>

                @if($canApprove && ($canSubmit ?? false))
                    <button class="btn btn-success"
                            onclick="return confirm('Sahkan penilaian ini? Status akan jadi PPK_APPROVED.')">
                        Sahkan (Finalize)
                    </button>
                @else
                    <button type="button" class="btn btn-success" disabled>Sahkan (Finalize)</button>

                    <div class="alert alert-warning mt-3 mb-0">
                        <div class="fw-bold">Belum boleh sahkan</div>
                        <div class="small text-muted">
                            @if(!$canApprove)
                                Status mesti <strong>PPP_SCORED</strong>.
                            @else
                                Sila lengkapkan bahagian wajib dahulu: <strong>{{ implode(', ', $missingSecs ?? []) }}</strong>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        @else
            <div class="alert alert-light">Bahagian ini untuk PPK sahaja. (Read-only)</div>
        @endif

    </div>
</div>
