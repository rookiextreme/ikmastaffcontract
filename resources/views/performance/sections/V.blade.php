@php
    $status = $evaluation->status ?? 'DRAFT';
    // PPP boleh isi selepas SUBMITTED, admin boleh edit bila-bila
    $locked = !(($roleKey === 'ppp' && in_array($status, ['SUBMITTED','PPP_DRAFT'])) || ($roleKey === 'admin'));
@endphp

<div class="card border mb-6">
    <div class="card-header">
        <h4 class="card-title mb-0">Bahagian V: Kompetensi & Markah (PPP)</h4>
    </div>
    <div class="card-body">

        @if(($items ?? collect())->isEmpty())
            <div class="alert alert-warning mb-0">
                Tiada item kompetensi. Sila seed / tambah kompetensi dahulu.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-row-bordered align-middle">
                    <thead>
                        <tr class="text-muted">
                            <th style="width:60px;">#</th>
                            <th>Kompetensi</th>
                            <th style="width:140px;">Markah (0-10)</th>
                            <th>Komen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $i => $it)
                            @php $sc = ($scores[$it->id] ?? null); @endphp
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $it->name }}</div>
                                    @if(!empty($it->description))
                                        <div class="text-muted small">{{ $it->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" min="0" max="10" step="1"
                                           name="sections[V][scores][{{ $it->id }}]"
                                           class="form-control"
                                           value="{{ old('sections.V.scores.'.$it->id, $sc->score ?? '') }}"
                                           {{ $locked ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="text"
                                           name="sections[V][comments][{{ $it->id }}]"
                                           class="form-control"
                                           value="{{ old('sections.V.comments.'.$it->id, $sc->comment ?? '') }}"
                                           {{ $locked ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-muted small mt-3">
                * PPP perlu isi sekurang-kurangnya satu markah sebelum hantar kepada PPK.
            </div>
        @endif

        @if($locked)
            <div class="alert alert-secondary mt-4 mb-0">Bahagian ini hanya boleh dikemaskini oleh PPP selepas PYD submit (atau Admin).</div>
        @endif
    </div>
</div>
