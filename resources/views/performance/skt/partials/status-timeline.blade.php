@php
    $e = $evaluation;
    $status = $status ?? ($e->status ?? 'DRAFT');

    $steps = [
        'DRAFT' => ['label'=>'PYD isi', 'done'=> true],
        'SUBMITTED' => ['label'=>'Hantar ke PPP', 'done'=> !empty($e->skt_submitted_at)],
        'PPP_SCORED' => ['label'=>'PPP sahkan', 'done'=> !empty($e->skt_ppp_reviewed_at)],
    ];
@endphp

<div class="mb-4">
    <div class="d-flex flex-wrap gap-2">
        @foreach($steps as $k => $s)
            <span class="badge {{ $s['done'] ? 'bg-success' : 'bg-light text-dark border' }}">
                {{ $s['label'] }}
            </span>
        @endforeach
        <span class="badge bg-light text-dark border ms-auto">
            Status semasa: {{ $status }}
        </span>
    </div>
</div>