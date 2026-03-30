@php
    $bahagian  = strtoupper((string)($bahagian ?? 'I'));
    $roleKey   = $roleKey ?? 'pyd';
    $status    = $status ?? ($evaluation->status ?? 'DRAFT');
    $is_locked = $is_locked ?? false;
@endphp

@if($bahagian === 'I')
    @include('performance.skt.sections.I', compact('evaluation','roleKey','status','is_locked','assignment','period'))
@elseif($bahagian === 'II')
    @include('performance.skt.sections.II', compact('evaluation','roleKey','status','is_locked','assignment','period'))
@elseif($bahagian === 'III')
    @include('performance.skt.sections.III', compact('evaluation','roleKey','status','is_locked','assignment','period'))
@else
    <div class="alert alert-warning">Bahagian tidak sah.</div>
@endif