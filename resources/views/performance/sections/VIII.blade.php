@php
    $roleKey = $roleKey ?? 'pyd';
    $evaluation = $evaluation ?? null;

    $requiredByRole = [
        'pyd' => ['I','II'],
        'ppp' => ['III','IV','V','VI','VIII'],
        'ppk' => ['III','IV','V','VI','IX'],
        'admin' => [],
    ];
    $isRequired = in_array('VIII', $requiredByRole[$roleKey] ?? []);

    $locked = !($roleKey === 'ppp' && $evaluation?->status === 'SUBMITTED');

    $saveRoute = $roleKey==='ppp' ? route('ppp.performance.save', $evaluation->id) : null;
    $submitRoute = $roleKey==='ppp' ? route('ppp.performance.submit', $evaluation->id) : null;
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0 text-primary">
            Bahagian VIII: Ulasan Keseluruhan & Pengesahan PPP
            @if($isRequired)
                <span style="color:#f1416c;font-weight:700;">*</span>
            @endif
        </h4>
    </div>

    <div class="card-body">

        <div class="alert alert-secondary">
            <div class="small text-muted">
                Bahagian ini untuk <strong>PPP</strong>. PYD/PPK/Admin view sahaja.
            </div>
        </div>

        <form method="POST" action="{{ $saveRoute ?? '#' }}">
            @csrf

            <div class="mb-4">
                <label class="form-label">
                    Ulasan PPP
                    <span style="color:#f1416c;font-weight:700;">*</span>
                </label>
                <textarea name="ppp_comment"
                          class="form-control"
                          rows="5"
                          {{ $locked ? 'disabled' : '' }}
                >{{ old('ppp_comment', $evaluation->ppp_comment) }}</textarea>
            </div>

            @if($roleKey==='ppp' && !$locked)
                <button class="btn btn-light-primary">Simpan</button>
            @endif

            @if($roleKey==='ppp' && $locked)
                <div class="alert alert-secondary mt-3">Tidak boleh isi kerana status tidak mengizinkan.</div>
            @endif
        </form>

        @if($roleKey==='ppp')
            <form method="POST" action="{{ $submitRoute }}" class="mt-4">
                @csrf
                <div class="alert alert-info">
                    <div class="fw-bold mb-1">Hantar kepada PPK</div>
                    <div class="small text-muted">
                        Sistem akan semak PPP lengkap Bahagian III–VI & VIII sebelum hantar.
                    </div>
                </div>

                <button class="btn btn-primary"
                        {{ $locked ? 'disabled' : '' }}
                        onclick="return confirm('Hantar kepada PPK?')">
                    Hantar ke PPK
                </button>
            </form>
        @endif

    </div>
</div>
