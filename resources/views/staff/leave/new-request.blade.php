@extends('layouts.backend.master')

@section('title')
    Permohonan Cuti
@endsection

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-body pt-9 pb-0">
            <!--begin::Details-->
            <div class="d-flex flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="flex-grow-1">
                    <!--begin::Title-->
                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                        <!--begin::User-->
                        <div class="d-flex flex-column">
                            <!--begin::Name-->
                            <div class="d-flex align-items-center mb-2">
                                <a class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">Maklumat Cuti Anda</a>
                                <i class="ki-duotone ki-verify fs-1 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Name-->
                        </div>
                        <!--end::User-->
                    </div>
                    <!--end::Title-->
                    <!--begin::Stats-->
                    <div class="d-flex flex-wrap flex-stack">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column flex-grow-1 pe-8">
                            <!--begin::Stats-->
                            <div class="d-flex flex-wrap">
    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->leave_total }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti Diambil</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->leave_taken }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Baki Cuti</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->leave_balance }} Hari</div>
        </div>
    </div>

    {{-- ===================== CUTI KELOMPOK (TAMBAH) ===================== --}}
    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti Kelompok</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->group_total }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti Kelompok Diambil</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->group_taken }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Baki Cuti Kelompok</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->group_balance }} Hari</div>
        </div>
    </div>
    {{-- =================== END CUTI KELOMPOK =================== --}}

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti Sakit</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->mc_total }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Jumlah Cuti Sakit Diambil</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->mc_taken }} Hari</div>
        </div>
    </div>

    <div class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
        <div class="fw-semibold fs-6 text-gray-700">Baki Cuti Sakit</div>
        <div class="d-flex align-items-center">
            <div class="fs-2 fw-bold">{{ $staff->getStaffPosition->getStaffLeave->mc_balance }} Hari</div>
        </div>
    
                                    <!--end::Number-->
                                </div>
                                <!--end::Stat-->
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Info-->
            </div>
            <!--end::Details-->
        </div>
    </div>

    <div class="row gx-5 gx-xl-10 mb-xl-10">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Permohonan Cuti</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-category" class="required form-label">Kategori Cuti</label>
                            <select class="form-control" id="leave-category" data-control="select2">
                                <option>Sila Pilih</option>
                                @foreach($leaveCategory as $lc)
                                    <option value="{{ $lc->id }}"data-mc="{{ $lc->is_mc }}"data-full="{{ $lc->is_full_day }}"data-half="{{ $lc->is_half_day }}"data-group="{{ $lc->is_group_leave ?? 0 }}">{{ $lc->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        {{-- ===================== JENIS CUTI KELOMPOK (TAMBAH) ===================== --}}
<div class="col-md-4 mb-4 vals-row mt-4" id="group-leave-type-wrap" style="display:none;">
    <label for="leave-group-type" class="required form-label">Jenis Cuti Kelompok</label>
    <select class="form-control" id="leave-group-type" data-control="select2">
        <option value="">Sila Pilih</option>
        @foreach($groupLeaveTypes as $t)
            <option value="{{ $t->id }}">{{ $t->name }}</option>
        @endforeach
    </select>
    <div class="invalid-feedback"></div>
</div>
{{-- =================== END JENIS CUTI KELOMPOK =================== --}}

                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-start-range" class="required form-label">Tarikh Mula Hingga Akhir Cuti</label>
                            <input type="text" id="leave-date-range" class="form-control" value="">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-approver" class="required form-label">Pelulus</label>
                            <select class="form-control" id="leave-approver">
                                <option>Sila Pilih</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-start-time" class="required form-label">Masa Mula</label>
                            <input type="text" class="form-control" id="leave-start-time" value="" disabled>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-end-time" class="required form-label">Masa Tamat</label>
                            <input type="text" class="form-control" id="leave-end-time" value="" disabled>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-4 vals-row mt-4">
                            <label for="leave-mc" class="required form-label">Lampiran - Sila masukkan sijil sakit atau surat temu janji hospital (jika berkaitan) </label>
                            <input type="file" class="form-control" id="leave-mc" name="leave_mc" disabled>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12 mb-4 vals-row mt-4">
                            <label for="leave-reason" class="form-label">Sebab bercuti</label>
                            <textarea id="leave-reason" rows="5" class="form-control"></textarea>
                            
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    @php
                        $saveDisabled = false;
                    @endphp
                    <button class="btn btn-success hover-scale me-2" id="store-update-leave-new-request" {{ $saveDisabled ? 'disabled' : '' }}>
                    <span class="indicator-label">
                        Hantar Permohonan
                    </span>
                        <span class="indicator-progress">
                        Sedang Diproses... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="staff-id" value="{{ $staff->id }}">
    <input type="hidden" id="position-branch-id" value="{{ $staff->getStaffPosition->branch_id }}">
    <input type="hidden" id="role-id" value="{{ $staff->getStaffPosition->branch_id }}">
@endsection

@section('jsExtensions')
    <script src="{{ asset('js/custom/modals.js') }}"></script>
@endsection

@section('jsCustom')
    <script>
        let moduleUrl = `staff/leave/`;
        let staff_id = $('#staff-id').val();
    </script>

    <script src="{{ asset('js/modules/staff/leave/new-request/init4.js') }}?v=6"></script>
    <script src="{{ asset('js/modules/staff/leave/new-request/index4.js') }}?v=6"></script>
@endsection
