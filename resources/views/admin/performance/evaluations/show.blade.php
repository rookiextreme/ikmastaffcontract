@extends('layouts.backend.master')

@section('title', 'Admin | Paparan Penilaian Prestasi')

@section('content')
@php
    $roleKey  = 'admin';
    $status   = $evaluation->status ?? 'DRAFT';
    $bahagian = strtoupper((string) request('bahagian', 'I'));

    $baseUrl = route('admin.performance.evaluations.show', $evaluation->id);
    $sectionMeta = $sectionMeta ?? [];
@endphp

<div class="card mb-6">
    <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="mb-1">Paparan Penilaian Prestasi (Admin)</h3>
                @php
    $pydGroup = strtoupper(
        trim((string) ($evaluation->assignment->pyd_group ?? ''))
    );
@endphp

<div class="text-muted">
    PYD:
    <strong>{{ $evaluation->assignment->pydUser->name ?? '-' }}</strong><br>

    PPP:
    {{ $evaluation->assignment->pppUser->name ?? '-' }}
    |
    PPK:
    {{ $evaluation->assignment->ppkUser->name ?? '-' }}<br>

    Kumpulan:

    @if($pydGroup === 'A')
        <span class="badge badge-light-success">
            Kumpulan Pengurusan &amp; Profesional (A)
        </span>
    @elseif($pydGroup === 'BC')
        <span class="badge badge-light-warning">
            Kumpulan Perkhidmatan Sokongan (B/C)
        </span>
    @else
        <span class="badge badge-light-secondary">
            Belum Ditetapkan
        </span>
    @endif

    <br>

    Status:

    {!! \App\Helpers\PerformanceHelper::statusBadge($status) !!}
</div>
            </div>

            {{-- ✅ TAMBAH: butang reset + finalize + kekalkan butang kembali (tiada code dibuang) --}}
            <div class="d-flex gap-2 flex-wrap">
                @if(in_array($status, ['SUBMITTED','PPP_SCORED','PPK_APPROVED'], true))
                    <button type="button"
                            class="btn btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#modalResetPppPpk">
                        Reset PPP & PPK
                    </button>
                @endif

                {{-- ✅ TAMBAH: butang reset PPK sahaja (PPP kekal, PPSM kekal) --}}
                @if(in_array($status, ['PPP_SCORED','PPK_APPROVED'], true))
                    <button type="button"
                            class="btn btn-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#modalResetPpk">
                        Reset PPK Sahaja
                    </button>
                @endif

                {{-- ✅ TAMBAH: butang finalize oleh admin --}}
                @if($status === 'PPK_APPROVED')
                    <button type="button"
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#modalFinalizeEvaluation">
                        Muktamadkan Penilaian
                    </button>
                @endif

                {{-- ✅ Butang PDF (hanya selepas FINAL) --}}
@if($status === 'FINAL')
    <a href="{{ route('admin.performance.evaluations.pdf', $evaluation->id) }}"
       target="_blank"
       class="btn btn-danger">
        <i class="fas fa-file-pdf me-1"></i>
        PDF Penilaian
    </a>
@endif

                <a href="{{ route('admin.performance.evaluations.index') }}"
                   class="btn btn-light">
                    Kembali
                </a>
            </div>
        </div>

        {{-- ✅ TAMBAH: alert kecil bila status sudah FINAL --}}
        @if($status === 'FINAL')
            <div class="alert alert-success mb-4">
                <strong>Penilaian telah dimuktamadkan.</strong><br>
                Rekod ini telah selesai di peringkat urus setia / admin dan status semasa ialah

{!! \App\Helpers\PerformanceHelper::statusBadge('FINAL') !!}
            </div>
        @endif

        {{-- ===============================
            TAB NAV BAHAGIAN
        ================================ --}}
        @include(
            'performance.partials.sections-nav',
            compact('bahagian','baseUrl','required','completedMap')
        )

        {{-- ===============================
            PAPARAN BORANG
            ✅ KEMASKINI: Jika Bahagian II & Admin => gunakan form update admin (override)
            ✅ Selain itu kekalkan paparan asal (read-only)
        ================================ --}}
        @if($roleKey === 'admin' && $bahagian === 'II')

            <form method="POST"
                  action="{{ route('admin.performance.evaluations.updatePydText', $evaluation->id) }}"
                  class="mt-6">
                @csrf

                {{-- controller tahu ini Bahagian II --}}
                <input type="hidden" name="bahagian" value="II">

                {{-- ✅ Render form Bahagian II (shared) dengan input name bahagian_ii[...] --}}
                @include(
                    'performance.partials.sections-render',
                    compact(
                        'bahagian',
                        'roleKey',
                        'evaluation',
                        'items',
                        'scores',
                        'sectionMeta'
                    )
                )

                {{-- Error validation --}}
                @if ($errors->any())
                    <div class="alert alert-danger mt-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-warning mb-4">
                    <strong>Perhatian (Admin Sahaja):</strong><br>
                    Kemaskini ini <u>tidak akan mengubah status penilaian</u> dan
                    <u>tidak menjejaskan penilaian PPP / PPK</u>.
                    Semua perubahan akan direkodkan dalam <em>log audit</em>.
                </div>

                {{-- ✅ Reason wajib (controller require) --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Sebab Kemaskini (Wajib)</label>
                    <textarea name="reason"
                              class="form-control"
                              rows="3"
                              required
                              minlength="5"
                              maxlength="1000"
                              placeholder="Contoh: Pembetulan maklumat latihan berdasarkan dokumen sokongan...">{{ old('reason') }}</textarea>
                    <div class="text-muted small mt-1">Minimum 5 aksara. Maksimum 1000 aksara.</div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Kemaskini Maklumat PYD (Admin)
                </button>
            </form>

        @else

            @include(
                'performance.partials.sections-render',
                compact(
                    'bahagian',
                    'roleKey',
                    'evaluation',
                    'items',
                    'scores',
                    'sectionMeta'
                )
            )

        @endif

        {{-- ===============================
            ✅ TAMBAHAN: LOG STATUS / TIMELINE
            (PYD / PPP / PPK / ADMIN / PPSM)
        ================================ --}}
        @include(
            'performance.partials.status-timeline',
            ['evaluation' => $evaluation]
        )

    </div>
</div>

{{-- ✅ TAMBAH: MODAL RESET PPP & PPK (letak bawah sekali, tiada code dibuang) --}}
@if(in_array($status, ['SUBMITTED','PPP_SCORED','PPK_APPROVED'], true))
<div class="modal fade" id="modalResetPppPpk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.performance.evaluations.reset-ppp-ppk', $evaluation->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Reset PPP & PPK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        Tindakan ini akan <strong>reset semua markah & ulasan PPP + PPK</strong>:
                        <ul class="mb-0 mt-2">
                            <li>Bahagian III–VI (markah kompetensi PPP & PPK)</li>
                            <li>Bahagian VIII (ulasan PPP)</li>
                            <li>Bahagian IX (ulasan PPK)</li>
                        </ul>
                        <div class="mt-2">
                            Status akan kembali kepada <strong>SUBMITTED</strong> (menunggu PPP isi semula).
                        </div>
                        <div class="mt-2">
                            <strong>PPSM tidak direset</strong> kerana admin boleh kemaskini semula bila perlu.
                        </div>
                    </div>

                    <label class="form-label fw-semibold">Sebab Reset (Wajib)</label>
                    <textarea name="reason"
                              rows="3"
                              class="form-control"
                              required
                              minlength="5"
                              maxlength="1000"
                              placeholder="Contoh: Arahan pengurusan untuk semakan semula penilaian...">{{ old('reason') }}</textarea>

                    @error('reason')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                            class="btn btn-danger"
                            onclick="return confirm('Pasti reset semua bahagian PPP & PPK untuk penilaian ini?')">
                        Ya, Reset
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

{{-- ✅ TAMBAH: MODAL RESET PPK SAHAJA (PPP kekal, PPSM kekal) --}}
@if(in_array($status, ['PPP_SCORED','PPK_APPROVED'], true))
<div class="modal fade" id="modalResetPpk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.performance.evaluations.reset-ppk', $evaluation->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Reset PPK Sahaja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        Tindakan ini akan <strong>reset PPK sahaja</strong>:
                        <ul class="mb-0 mt-2">
                            <li>Bahagian III–VI: markah PPK (ppk_score) dan ulasan PPK (jika ada)</li>
                            <li>Bahagian IX: ulasan PPK</li>
                        </ul>
                        <div class="mt-2">
                            Status akan kembali kepada <strong>PPP_SCORED</strong> (PPK boleh isi semula).
                        </div>
                        <div class="mt-2">
                            <strong>PPP tidak direset</strong> dan <strong>PPSM kekal</strong>.
                        </div>
                    </div>

                    <label class="form-label fw-semibold">Sebab Reset (Wajib)</label>
                    <textarea name="reason"
                              rows="3"
                              class="form-control"
                              required
                              minlength="5"
                              maxlength="1000"
                              placeholder="Contoh: PPK tersalah isi ulasan / markah, perlu isi semula...">{{ old('reason') }}</textarea>

                    @error('reason')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                            class="btn btn-warning"
                            onclick="return confirm('Pasti reset PPK sahaja untuk penilaian ini?')">
                        Ya, Reset PPK
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

{{-- ✅ TAMBAH: MODAL FINALIZE / MUKTAMADKAN PENILAIAN --}}
@if($status === 'PPK_APPROVED')
<div class="modal fade" id="modalFinalizeEvaluation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST" action="{{ route('admin.performance.evaluations.finalize', $evaluation->id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Muktamadkan Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-success">
                        Tindakan ini akan menukar status penilaian daripada
                        <strong>PPK_APPROVED</strong> kepada <strong>FINAL</strong>.
                        <div class="mt-2">
                            Gunakan tindakan ini apabila penilaian telah selesai disemak oleh PPK dan
                            urus setia / admin ingin memuktamadkan rekod sebagai keputusan rasmi.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sebab Muktamad (Wajib)</label>
                        <textarea name="reason"
                                  rows="3"
                                  class="form-control"
                                  required
                                  minlength="5"
                                  maxlength="1000"
                                  placeholder="Contoh: Penilaian telah lengkap disahkan oleh PPK dan dimuktamadkan oleh urus setia...">{{ old('reason') }}</textarea>
                        <div class="text-muted small mt-1">
                            Minimum 5 aksara. Maksimum 1000 aksara.
                        </div>
                    </div>

                    @error('reason')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                            class="btn btn-success"
                            onclick="return confirm('Pasti mahu muktamadkan penilaian ini? Selepas dimuktamadkan, status akan menjadi FINAL.')">
                        Ya, Muktamadkan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endif

@endsection