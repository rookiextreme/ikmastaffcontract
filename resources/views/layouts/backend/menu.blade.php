@php use App\Models\Agent;use App\Models\AgentStatus;use Illuminate\Support\Facades\Auth; @endphp
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo" style="background-color: lightgrey">
        <!--begin::Logo image-->
        <a>
            <img alt="Logo" src="{{ asset('assets/images/ikmalogo.png') }}"
                 class="h-70px ps-15 app-sidebar-logo-default"/>
            <img alt="Logo" src="{{ asset('assets/images/ikmalogo.png') }}"
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
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                     data-kt-menu="true" data-kt-menu-expand="false">
                    <!--end:Menu item-->
                    @role('staff')
                    <div class="menu-item pt-5">
                        <!--begin:Menu content-->
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Profil</span>
                        </div>
                        <!--end:Menu content-->
                    </div>
                    <div class="menu-item">
                        <a class="menu-link"
                           href="{{ route('staff.profile', ['user_id' => Auth::user(), 'page' => 'main']) }}">
                            <span class="menu-icon">
                            <i class="ki-duotone ki-abstract-13 fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            </i>
                            </span>
                            <span class="menu-title">Profil</span>
                        </a>
                    </div>
                    @endrole

{{--                    @role('super-admin|admin')--}}
{{--                        <div class="menu-item pt-5">--}}
{{--                            <!--begin:Menu content-->--}}
{{--                            <div class="menu-content">--}}
{{--                                <span class="menu-heading fw-bold text-uppercase fs-7">Tetapan Sistem</span>--}}
{{--                            </div>--}}
{{--                            <!--end:Menu content-->--}}
{{--                        </div>--}}
{{--                        <div class="menu-item">--}}
{{--                            <a class="menu-link"--}}
{{--                               href="{{ route('admin.setting.course-type') }}">--}}
{{--                                    <span class="menu-icon">--}}
{{--                                    <i class="ki-duotone ki-abstract-13 fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    </i>--}}
{{--                                    </span>--}}
{{--                                <span class="menu-title">Jenis Kursus</span>--}}
{{--                            </a>--}}
{{--                            <a class="menu-link"--}}
{{--                               href="{{ route('admin.setting.course-category') }}">--}}
{{--                                    <span class="menu-icon">--}}
{{--                                    <i class="ki-duotone ki-abstract-13 fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    </i>--}}
{{--                                    </span>--}}
{{--                                <span class="menu-title">Kategori Kursus</span>--}}
{{--                            </a>--}}
{{--                            <a class="menu-link"--}}
{{--                               href="{{ route('admin.setting.position') }}">--}}
{{--                                    <span class="menu-icon">--}}
{{--                                    <i class="ki-duotone ki-abstract-13 fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    </i>--}}
{{--                                    </span>--}}
{{--                                <span class="menu-title">Jawatan Dalaman</span>--}}
{{--                            </a>--}}
{{--                            <a class="menu-link"--}}
{{--                               href="{{ route('admin.setting.course-tagging') }}">--}}
{{--                                    <span class="menu-icon">--}}
{{--                                    <i class="ki-duotone ki-abstract-13 fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    </i>--}}
{{--                                    </span>--}}
{{--                                <span class="menu-title">Tags</span>--}}
{{--                            </a>--}}
{{--                            <a class="menu-link"--}}
{{--                               href="{{ route('admin.setting.faq') }}">--}}
{{--                                    <span class="menu-icon">--}}
{{--                                    <i class="ki-duotone ki-abstract-13 fs-2">--}}
{{--                                    <span class="path1"></span>--}}
{{--                                    <span class="path2"></span>--}}
{{--                                    </i>--}}
{{--                                    </span>--}}
{{--                                <span class="menu-title">FAQ</span>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    @endrole--}}
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
