@php
    $bahagian = $bahagian ?? request('bahagian', 'I');

    // ✅ fallback kalau parent tak pass
    $baseUrl = $baseUrl ?? url()->current();

    // Semua bahagian muncul
    $sections = ['I','II','III','IV','V','VI','VII','VIII','IX'];

    $required = $required ?? [];
    $completedMap = $completedMap ?? [];
@endphp

<div class="mb-4">
    <div class="btn-group flex-wrap">
        @foreach($sections as $sec)
            @php
                $isActive = ($bahagian === $sec);
                $isReq = in_array($sec, $required);
            @endphp

            <a class="btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-light' }}"
               href="{{ $baseUrl }}?bahagian={{ $sec }}">
                Bahagian {{ $sec }}
                @if($isReq)
                    <span class="text-danger">*</span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="text-muted small mt-2">
        <span class="text-danger">*</span> = Bahagian wajib untuk peranan ini.
        Bahagian VII auto (paparan jumlah/purata) — tidak perlu isi.
    </div>
</div>
