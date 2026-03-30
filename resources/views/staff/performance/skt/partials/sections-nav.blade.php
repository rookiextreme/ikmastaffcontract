@php
    $bahagian = strtoupper((string)($bahagian ?? 'I'));
    $baseUrl  = $baseUrl ?? route('staff.performance.skt');

    $tabs = [
        'I'   => 'Bahagian I',
        'II'  => 'Bahagian II',
        'III' => 'Bahagian III',
    ];

    // =========================
    // ✅ KIRA STATUS LENGKAP (guna repository)
    // =========================
    $completedMap = [];

    if(isset($evaluation) && $evaluation) {
        $repo = app(\App\Repositories\Performance\PerformanceEvaluationRepository::class);

        foreach(array_keys($tabs) as $code) {
            $completedMap[$code] = $repo->isSectionComplete($code, 'pyd', $evaluation);
        }
    }
@endphp

<ul class="nav nav-tabs mb-4">
    @foreach($tabs as $code => $label)
        @php
            $isActive = $bahagian === $code;
            $isDone   = (bool)($completedMap[$code] ?? false);
        @endphp

        <li class="nav-item">
            <a class="nav-link {{ $isActive ? 'active' : '' }}"
               href="{{ $baseUrl }}?bahagian={{ $code }}">

                {{ $label }}

                {{-- ✅ TANDA ✓ BILA LENGKAP --}}
                @if($isDone)
                    <span class="ms-2 badge bg-success">✓</span>
                @endif

            </a>
        </li>
    @endforeach
</ul>

<div class="text-muted small mb-4">
    * Sila isi Bahagian I dahulu. Bahagian II: Aktiviti Ditambah wajib diisi.
    Bahagian Gugur adalah pilihan.
</div>