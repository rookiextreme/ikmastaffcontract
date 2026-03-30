@php
    $bahagian  = strtoupper((string)($bahagian ?? 'I'));

    // pastikan variable wujud & konsisten
    $evaluation = $evaluation ?? null;
    $roleKey   = $roleKey ?? 'pyd';
    $status    = $status ?? ($evaluation->status ?? 'DRAFT');
    $is_locked = $is_locked ?? false;
@endphp

@if($bahagian === 'I')
    @include('staff.performance.skt.sections.bahagian-i', [
        'evaluation' => $evaluation,
        'roleKey'    => $roleKey,
        'status'     => $status,
        'is_locked'  => $is_locked,
    ])
@elseif($bahagian === 'II')
    @include('staff.performance.skt.sections.bahagian-ii', [
        'evaluation' => $evaluation,
        'roleKey'    => $roleKey,
        'status'     => $status,
        'is_locked'  => $is_locked,
    ])
@elseif($bahagian === 'III')
    @include('staff.performance.skt.sections.bahagian-iii', [
        'evaluation' => $evaluation,
        'roleKey'    => $roleKey,
        'status'     => $status,
        'is_locked'  => $is_locked,
    ])
@else
    <div class="alert alert-warning">Bahagian tidak sah.</div>
@endif