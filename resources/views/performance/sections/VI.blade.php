@php
    $roleKey = $roleKey ?? 'pyd';
    $evaluation = $evaluation ?? null;
    $items = $items ?? collect();
    $scores = $scores ?? collect();

    $requiredByRole = [
        'pyd' => ['I','II'],
        'ppp' => ['III','IV','V','VI','VIII'],
        'ppk' => ['III','IV','V','VI','IX'],
        'admin' => [],
    ];
    $isRequired = in_array('VI', $requiredByRole[$roleKey] ?? []);

    $locked = true;
    if($roleKey === 'ppp' && $evaluation?->status === 'SUBMITTED') $locked = false;
    if($roleKey === 'ppk' && $evaluation?->status === 'PPP_SCORED') $locked = false;

    $saveRoute = null;
    if($roleKey === 'ppp'){
        $saveRoute = route('ppp.performance.save', $evaluation->id);
    } elseif($roleKey === 'ppk'){
        $saveRoute = route('ppk.performance.approve', $evaluation->id);
    }
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0 text-primary">
            Bahagian VI: Kegiatan & Sumbangan Rasmi
            @if($isRequired)
                <span style="color:#f1416c;font-weight:700;">*</span>
            @endif
        </h4>
    </div>

    <div class="card-body">
        <div class="alert alert-secondary"><div class="small text-muted">PPP/PPK isi markah. PYD view.</div></div>

        <form method="POST" action="{{ $saveRoute ?? '#' }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-row-bordered align-middle">
                    <thead>
                    <tr class="text-muted">
                        <th style="width:60px;">#</th>
                        <th>Kompetensi</th>
                        <th style="width:160px;">Markah (0-10) <span style="color:#f1416c;font-weight:700;">*</span></th>
                        <th>Komen (optional)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $i => $it)
                        @php
                            $sc = $scores[$it->id] ?? null;
                            $valScore = $roleKey==='ppk' ? ($sc?->ppk_score ?? $sc?->score ?? null) : ($sc?->score ?? null);
                            $valComment = $roleKey==='ppk' ? ($sc?->ppk_comment ?? $sc?->comment ?? null) : ($sc?->comment ?? null);
                        @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>
                                <div class="fw-semibold">{{ $it->name }}</div>
                                @if($it->description)<div class="text-muted small">{{ $it->description }}</div>@endif
                            </td>
                            <td>
                                <input type="number" name="scores[{{ $it->id }}]" min="0" max="10"
                                       class="form-control"
                                       value="{{ old('scores.'.$it->id, $valScore) }}"
                                       {{ ($locked || $roleKey==='pyd' || $roleKey==='admin') ? 'disabled' : '' }}>
                            </td>
                            <td>
                                <input type="text" name="comments[{{ $it->id }}]" class="form-control"
                                       value="{{ old('comments.'.$it->id, $valComment) }}"
                                       {{ ($locked || $roleKey==='pyd' || $roleKey==='admin') ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-6">Tiada item.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if(($roleKey==='ppp' || $roleKey==='ppk') && !$locked)
                <button class="btn btn-light-primary">Simpan</button>
            @endif

            @if(($roleKey==='ppp' || $roleKey==='ppk') && $locked)
                <div class="alert alert-secondary mt-4">Tidak boleh isi kerana status tidak mengizinkan.</div>
            @endif
        </form>
    </div>
</div>
