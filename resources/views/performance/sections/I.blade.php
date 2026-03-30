@php
    $roleKey = $roleKey ?? 'pyd';
    $evaluation = $evaluation ?? null;

    $requiredByRole = [
        'pyd' => ['I','II'],
        'ppp' => ['III','IV','V','VI','VIII'],
        'ppk' => ['III','IV','V','VI','IX'],
        'admin' => [],
    ];
    $isRequired = in_array('I', $requiredByRole[$roleKey] ?? []);

    $assignment = $evaluation?->assignment;
    $period = $evaluation?->period;

    $status = $evaluation?->status ?? '-';
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0 text-primary">
            Bahagian I: Maklumat Penilaian
            @if($isRequired)
                <span style="color:#f1416c;font-weight:700;">*</span>
            @endif
        </h4>
    </div>

    <div class="card-body">

        <div class="row g-4">
            <div class="col-md-6">
                <label class="form-label">Nama PYD</label>
                <input class="form-control" value="{{ $assignment?->pydUser?->name ?? '-' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tempoh (Tahun)</label>
                <input class="form-control" value="{{ $period?->year ?? '-' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <input class="form-control" value="{{ $status }}" disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label">PPP</label>
                <input class="form-control" value="{{ $assignment?->pppUser?->name ?? '-' }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">PPK</label>
                <input class="form-control" value="{{ $assignment?->ppkUser?->name ?? '-' }}" disabled>
            </div>
        </div>

        <div class="text-muted small mt-4">
            * Bahagian ini paparan maklumat sahaja (read-only).
        </div>
    </div>
</div>
