@php
    // Pastikan variable wujud
    $roleKey = $roleKey ?? 'admin';
    $evaluation = $evaluation ?? null;
    $items = $items ?? collect();
    $scores = $scores ?? collect();
@endphp

@include('performance.partials.sections-render', [
    'roleKey'    => $roleKey,
    'evaluation' => $evaluation,
    'items'      => $items,
    'scores'     => $scores,
])
