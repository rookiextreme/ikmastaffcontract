@php use App\Models\ApplicantStatus;use Illuminate\Support\Facades\Auth; @endphp
@extends('layouts.backend.master')
@section('title')
    {{ $staff->getUser->name }} - Profil
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
                                <a class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">Profil</a>
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
                                <!--begin::Stat-->
                                <div
                                    class="border border-gray-300 border-dashed rounded min-w-auto py-3 px-4 me-6 mb-3">
                                    <div class="fw-semibold fs-6 text-gray-700">Umur</div>
                                    <!--begin::Number-->
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold">{{ $staff->dob ? date('Y') - date('Y', strtotime($staff->dob)) : '-' }}</div>
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
            <!--begin::Navs-->
            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                <!--begin::Nav item-->
                <li class="nav-item mt-2">
                    <a class="nav-link text-active-primary ms-0 me-10 py-5 {{ $page == 'profile' ? 'active' : '' }}"
                       href="{{ route('staff.profile', ['user_id' => $staff->user_id, 'page' => 'main']) }}">Rekod Peribadi</a>
                </li>
{{--                <li class="nav-item mt-2">--}}
{{--                    <a class="nav-link text-active-primary ms-0 me-10 py-5 {{ $page == 'resetpassword' ? 'active' : '' }}"--}}
{{--                       href="{{ route('staff.profile', ['user_id' => $staff->user_id, 'page' => 'resetpassword']) }}">Tetapan Kata Laluan</a>--}}
{{--                </li>--}}
                <!--end::Nav item-->
                <!--begin::Nav item-->
                @if($staff->profile_complete == 1)
{{--                    <li class="nav-item mt-2">--}}
{{--                        <a class="nav-link text-active-primary ms-0 me-10 py-5 {{ $page == 'academic' ? 'active' : '' }}"--}}
{{--                           href="{{ route('staff.profile', ['user_id' => $staff->user_id, 'page' => 'academic']) }}">--}}
{{--                            @if(!$staff->academic_complete)--}}
{{--                                <span class="badge badge-circle badge-outline badge-danger me-2">!</span>--}}
{{--                            @endif--}}
{{--                            Akademik--}}
{{--                        </a>--}}
{{--                    </li>--}}
                @endif
            </ul>
            <!--begin::Navs-->
        </div>
    </div>
    @if($page == 'main')
{{--        @include('staff.profile.tabs.profile-tab')--}}
    @elseif($page == 'academic')
        @include('staff.profile.modals.academic-modal')
        @include('staff.profile.tabs.academic-tab')
    @elseif($page == 'resetpassword')
        @include('staff.profile.tabs.password-tab')
    @endif
    <input type="hidden" id="staff-id" value="{{ $staff->id }}">
    <input type="hidden" id="user-id" value="{{ $staff->getUser->id }}">
    <input type="hidden" id="page" value="{{ $page }}">
@endsection

@section('jsExtensions')
    <script src="{{ asset('js/custom/modals.js') }}"></script>
@endsection

@section('jsCustom')
    <script>
        let moduleUrl = 'staff/profile/';
        let staff_id = $('#staff-id').val();
        let user_id = $('#user-id').val();
        let page = $('#page').val();
    </script>

    @if($page == 'main')
        <script src="{{ asset('js/modules/staff/profile/init.js') }}"></script>
        <script src="{{ asset('js/modules/staff/profile/index.js') }}"></script>
    @elseif($page == 'academic')
        <script src="{{ asset('js/custom/datatable-helper.js') }}"></script>
        <script src="{{ asset('js/modules/staff/academic/init.js') }}"></script>
        <script src="{{ asset('js/modules/staff/academic/index.js') }}"></script>
    @endif
@endsection

