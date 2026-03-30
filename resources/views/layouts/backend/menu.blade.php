@php
    use App\Models\Agent;
    use App\Models\AgentStatus;
    use Illuminate\Support\Facades\Auth;

    // ✅ Tapis menu PPP/PPK berdasarkan lantikan dalam tempoh aktif
    use App\Models\PerformanceAssignment;
    use App\Models\PerformancePeriod;

    $uid = Auth::id();
    $activePeriod = PerformancePeriod::where('is_active', 1)->first();

    $isPPP = false;
    $isPPK = false;

    if ($activePeriod) {
        $isPPP = PerformanceAssignment::where('performance_period_id', $activePeriod->id)
            ->where('ppp_user_id', $uid)
            ->exists();

        $isPPK = PerformanceAssignment::where('performance_period_id', $activePeriod->id)
            ->where('ppk_user_id', $uid)
            ->exists();
    }
@endphp
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo" style="background-color: color(srgb 0.0501 0.0551 0.0721)">
        <a>
            <img alt="Logo" src="{{ asset('assets/images/final.png') }}"
                 class="app-sidebar-logo-default mb-0 mt-2" style="height: 125px;width: 200px"/>
            <img alt="Logo" src="{{ asset('assets/images/final.png') }}"
                 class="h-20px app-sidebar-logo-minimize"/>
        </a>

        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
    </div>
    <!--end::Logo-->

    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">

                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                     data-kt-menu="true" data-kt-menu-expand="false">

                    {{-- ========================================================= --}}
                    {{-- ADMIN / SUPER ADMIN --}}
                    {{-- ========================================================= --}}
                    @role('super-admin|admin')

                        {{-- DASHBOARD --}}
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.dashboard') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-home fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Papan Pemuka</span>
                            </a>
                        </div>

                        {{-- PROFIL --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Profil</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link"
                               href="{{ route('staff.profile', ['user_id' => Auth::user(), 'page' => 'resetpassword']) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Tetapan Kata Laluan</span>
                            </a>
                        </div>

                        {{-- CUTI --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Cuti</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('staff.leave.approval', ['user_id' => Auth::user()->id]) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Pengesahan Cuti</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.group_leave.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Cuti Kelompok</span>
                            </a>
                        </div>

                        {{-- PRESTASI (ADMIN) --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Prestasi</span>
                            </div>
                        </div>

                        {{-- ✅ TAMBAH: Dashboard Prestasi --}}
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.performance.dashboard') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-element-11 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                        <span class="path3"></span><span class="path4"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Dashboard Prestasi</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.performance.periods.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Tempoh Penilaian</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.performance.assignments.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Lantikan PPP/PPK</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.performance.evaluations.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Penilaian Prestasi</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.performance.logs.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-notepad fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Log Prestasi</span>
                            </a>
                        </div>


                        {{-- PENEMPATAN --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Penempatan</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.branch.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Penempatan</span>
                            </a>
                        </div>

                        {{-- PENGGUNA --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Pengguna</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.user.list') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Senarai Pengguna</span>
                            </a>
                        </div>

                        {{-- MAKLUMAT KAKITANGAN --}}
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.staff.stats') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Maklumat Kakitangan</span>
                            </a>
                        </div>

                        {{-- TETAPAN --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Tetapan</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.setting.position.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Jawatan</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.setting.grade.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Gred</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.setting.unit.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Unit</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.setting.publicholiday.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Cuti Umum</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.setting.weekendholiday.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Cuti Biasa Mengikut Negeri</span>
                            </a>
                        </div>

                        {{-- LAPORAN --}}
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Laporan</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.reporting.index') }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Sejarah Jawatan</span>
                            </a>
                        </div>

                    @endrole


                    {{-- ========================================================= --}}
                    {{-- STAFF / PYD --}}
                    {{-- ========================================================= --}}
                    @role('staff|ketua_unit|penolong_pengarah|ketua_pengarah')

                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Profil</span>
                            </div>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link"
                               href="{{ route('staff.profile', ['user_id' => Auth::user(), 'page' => 'main']) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Profil</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link"
                               href="{{ route('staff.profile', ['user_id' => Auth::user(), 'page' => 'resetpassword']) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Tetapan Kata Laluan</span>
                            </a>
                        </div>

                        {{-- PRESTASI (PYD + PPP jika role) --}}
<div class="menu-item pt-5">
    <div class="menu-content">
        <span class="menu-heading fw-bold text-uppercase fs-7">Prestasi</span>
    </div>
</div>

<div class="menu-item">
    <a class="menu-link" href="{{ route('staff.performance.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-abstract-13 fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Penilaian Prestasi</span>
    </a>
</div>

{{-- ✅ TAMBAH: SKT (PYD) --}}
<div class="menu-item">
    <a class="menu-link" href="{{ route('staff.performance.skt', ['bahagian' => 'I']) }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-clipboard-check fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">SKT</span>
    </a>
</div>

{{-- ✅ Menu PPK (TAPIS ikut lantikan) --}}
@if($isPPK)
<div class="menu-item">
    <a class="menu-link {{ request()->is('ppk/performance*') ? 'active' : '' }}"
       href="{{ route('ppk.performance.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-chart-line fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Penilaian Prestasi (PPK)</span>
    </a>
</div>
@endif

{{-- ✅ Menu PPP (TAPIS ikut lantikan) --}}
@if($isPPP)
<div class="menu-item">
    <a class="menu-link" href="{{ route('ppp.performance.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-abstract-13 fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Penilaian Prestasi (PPP)</span>
    </a>
</div>

{{-- ✅ TAMBAH: Penilaian SKT (PPP) --}}
<div class="menu-item">
    <a class="menu-link" href="{{ route('ppp.performance.skt.index') }}">
        <span class="menu-icon">
            <i class="ki-duotone ki-clipboard-check fs-2">
                <span class="path1"></span><span class="path2"></span>
            </i>
        </span>
        <span class="menu-title">Penilaian SKT (PPP)</span>
    </a>
</div>
@endif
                        {{-- CUTI (ikut logic sedia ada) --}}
                        @php
                            $showCuti = false;
                            $user = Auth::user();
                            $position = $user->getStaff->getStaffPosition ?? null;

                            if($position){
                                if($position->getBranch->hq ?? false){
                                    $showCuti = true;
                                }else{
                                    if($user->hasRole('staff')){
                                        $showCuti = true;
                                    }
                                }
                            }
                        @endphp

                        @if($showCuti)
                            @if(optional(Auth::user()->getStaff->getStaffPosition->getStaffLeave)->leave_total != null)
                                <div class="menu-item pt-5">
                                    <div class="menu-content">
                                        <span class="menu-heading fw-bold text-uppercase fs-7">Cuti</span>
                                    </div>
                                </div>

                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('staff.leave.request', ['user_id' => Auth::user()->id]) }}">
                                        <span class="menu-icon">
                                            <i class="ki-duotone ki-abstract-13 fs-2">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <span class="menu-title">Senarai Permohonan</span>
                                    </a>
                                </div>

                                <div class="menu-item">
                                    <a class="menu-link" href="{{ route('staff.leave.new-request', ['user_id' => Auth::user()->id]) }}">
                                        <span class="menu-icon">
                                            <i class="ki-duotone ki-abstract-13 fs-2">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <span class="menu-title">Permohonan Cuti</span>
                                    </a>
                                </div>
                            @endif
                        @endif

                    @endrole


                    {{-- ========================================================= --}}
                    {{-- KETUA UNIT / PENOLONG PENGARAH / KETUA PENGARAH (Pelulus Cuti) --}}
                    {{-- ========================================================= --}}
                    @role('ketua_unit|penolong_pengarah|ketua_pengarah')
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('staff.leave.approval', ['user_id' => Auth::user()->id]) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Pengesahan Cuti</span>
                            </a>
                        </div>
                    @endrole


                    {{-- ========================================================= --}}
                    {{-- APPROVAL ADMIN --}}
                    {{-- ========================================================= --}}
                    @role('approval-admin')
                        <div class="menu-item pt-5">
                            <div class="menu-content">
                                <span class="menu-heading fw-bold text-uppercase fs-7">Pengesahan Cuti</span>
                            </div>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('approval-admin.leave.request', ['user_id' => Auth::user()->id]) }}">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-abstract-13 fs-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Senarai Permohonan</span>
                            </a>
                        </div>
                    @endrole

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>