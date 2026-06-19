<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Library\Datatable\SymTable;
use App\Models\BranchPosition;
use App\Models\StaffAcademic;
use App\Models\StaffFamily;
use App\Models\StaffHarta;
use App\Models\User;
use App\Repositories\BranchPositionRepository;
use App\Repositories\BranchRepository;
use App\Repositories\StaffLeaveRepository;
use App\Repositories\StaffPositionRepository;
use App\Repositories\StaffRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;


class StaffController extends Controller
{
    use CommonTrait, LookupTrait;

    private StaffRepository $staffRepository;
    private BranchRepository $branchRepository;
    private BranchPositionRepository $branchPositionRepository;
    private StaffPositionRepository $staffPositionRepository;
    private StaffLeaveRepository $staffLeaveRepository;

    public function __construct(
        StaffRepository $staffRepository,
        BranchRepository $branchRepository,
        BranchPositionRepository $branchPositionRepository,
        StaffPositionRepository $staffPositionRepository,
        StaffLeaveRepository $staffLeaveRepository
    ){
        $this->staffRepository = $staffRepository;
        $this->branchRepository = $branchRepository;
        $this->branchPositionRepository = $branchPositionRepository;
        $this->staffPositionRepository = $staffPositionRepository;
        $this->staffLeaveRepository = $staffLeaveRepository;
    }

    public function index($user_id, $page, Request $request)
    {
        $staff = $this->staffRepository->getStaffProfile($user_id);

        $responseData = [
            'page' => $page,
            'user_id' => $user_id,
            'staff' => $staff,
            'families' => StaffFamily::where('staff_id', $staff->id)->get(),
            'login_user_name' => $staff->getUser->name
        ];

        if($page == 'main'){
            $checkPositionRecord = $this->staffPositionRepository->checkExistRecord($staff->id);
            $this->staffLeaveRepository->checkExistRecord($checkPositionRecord->id);
            $responseData['staff'] = $this->staffRepository->getStaffProfile($user_id);

            $country = $this->getCountries();
            $state = $this->getStates();
            $race = $this->getRaces();
            $maritalStatus = $this->getMaritalStatus();
            $bumiputera = $this->getBumiputeras();
            $religion = $this->getReligion();
            $salutation = $this->getSalutations();
            $gender = $this->getGenders();

            $responseData['country'] = $country;
            $responseData['state'] = $state;
            $responseData['race'] = $race;
            $responseData['marital_status'] = $maritalStatus;
            $responseData['bumiputera'] = $bumiputera;
            $responseData['religion'] = $religion;
            $responseData['salutation'] = $salutation;
            $responseData['gender'] = $gender;
        }elseif($page == 'academic'){
            $academicQualifications = $this->getAcademicQualifications();
            $responseData['academic_qualifications'] = $academicQualifications;
        }elseif($page == 'position'){
            $state = $this->getStates();
            $responseData['state_select'] = $request->state_select ?? null;
            $responseData['branch_select'] = $request->branch_select ?? null;

            if($request->branch_select){
                $responseData['branch_record'] = $this->branchRepository->getBranch($request->branch_select);
            }

            $responseData['state'] = $state;
        }

        return view('staff.profile.index')->with($responseData);
    }

    // ================= MAIN PROFILE =================
    public function storeUpdateMain(Request $request)
    {
        $m = $this->staffRepository->storeUpdateProfile($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    // ================= ACADEMIC =================
    public function academicList(Request $request)
    {
        $model = $this->staffRepository->getAcademicList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => function($data){
                    return $data->id;
                }
            ])
            ->addColumn('level', function($data){
                return $data->qualification;
            })
            ->addColumn('institution', function($data){
                return $data->institution_name;
            })
            ->addColumn('certificate', function($data){
                $pro = $data->certification_professional
                    ? '<a class="text-warning" target="_blank" href="'.asset('uploads/staff/academics/cert_pro/'.$data->certification_professional).'">Papar Sijil</a>'
                    : '';

                $cert = $data->certificate_file
                    ? '<br><a target="_blank" href="'.asset('uploads/staff/academics/cert/'.$data->certificate_file).'">Papar Sijil</a>'
                    : '';

                if($pro == null && $cert == null){
                    return '-';
                }

                return $pro.$cert;
            })
            ->addColumn('specialization', function($data){
                return '<span class="text-primary">'.ucwords($data->major_specialization).'</span>'
                    .($data->minor_specialization ? '<br><span class="text-info">'.ucwords($data->minor_specialization).'</span>' : '');
            })
            ->addColumn('grade', function($data){
                return $data->overall_grade ?? '-';
            })
            ->make();
    }

    public function storeUpdateAcademic(Request $request)
    {
        $m = $this->staffRepository->storeUpdateAcademic($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function getAcademicInfo(Request $request) : JsonResponse
    {
        return $this->setDataResponse($this->staffRepository->getAcademic($request->id));
    }

    public function deleteAcademic(Request $request) : JsonResponse
    {
        return $this->setResponse($this->setHardDelete(StaffAcademic::class, $request->id, 'Akademik'));
    }

    // ================= RESET PASSWORD =================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => 'Kata Laluan Wajib Diisi',
            'password.confirmed' => 'Kata Laluan Tidak Sama',
            'password.min' => 'Kata Laluan Perlu Minima 8 Karakter',
        ]);

        $user_id = $request->user_id;

        $m = User::find($user_id);
        $m->password = Hash::make($request->password);
        $m->save();

        return redirect()->back()->with('success', 'Kata laluan berjaya dikemaskini!');
    }

    // ================= POSITION / BRANCH =================
    public function getBranchByState(Request $request)
    {
        return json_encode([
            'items' => $this->branchRepository->getBranchesByState($request)
        ]);
    }

    public function getPositionByBranch(Request $request)
    {
        return json_encode([
            'items' => $this->branchPositionRepository->getPositionByBranch($request)
        ]);
    }

    public function storeUpdatePosition(Request $request)
    {
        $m = $this->staffPositionRepository->storeUpdatePosition($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function storeUpdateNewLeaveBalance(Request $request)
    {
        $m = $this->staffLeaveRepository->storeUpdateNewLeaveBalance($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    // ================= FAMILY =================
    public function familyList(Request $request)
    {
        $model = $this->staffRepository->getFamilyList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => function($data){
                    return $data->id;
                }
            ])
            ->addColumn('name', function($data){
                return $data->name.'<br>Umur '.(Carbon::parse($data->dob)->age);
            })
            ->addColumn('email', function($data){
                return $data->email.'<br>'.$data->phone;
            })
            ->addColumn('relation', function($data){
                return $data->relation;
            })
            ->addColumn('grade', function($data){
                return $data->overall_grade ?? '-';
            })
            ->addColumn('death', function($data){
                return $data->death_date ? $this->regularDate($data->death_date) : '-';
            })
            ->make();
    }

    public function storeUpdateFamily(Request $request)
    {
        $m = $this->staffRepository->storeUpdateFamily($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function getFamilyInfo(Request $request) : JsonResponse
    {
        return $this->setDataResponse($this->staffRepository->getFamily($request->id));
    }

    public function deleteFamily(Request $request) : JsonResponse
    {
        return $this->setResponse($this->setHardDelete(StaffFamily::class, $request->id, 'Maklumat Keluarga'));
    }

    // ================= HARTA =================
    public function hartaList(Request $request){
    $model = $this->staffRepository->getHartaList($request);

    return SymTable::of($model)
        ->addRowAttr([
            'data-id' => fn($data) => $data->id
        ])
        ->addColumn('owner', function($data){

            // 👉 AHLI KELUARGA
            if(($data->owner_type ?? 'self') == 'family'){
                $familyName = $data->family_name ?? '-';
                $familyRelation = $data->family_relation ?? '';

                return $familyRelation
                    ? $familyName.' ('.$familyRelation.')'
                    : $familyName;
            }

            // 👉 LAIN-LAIN
            if(($data->owner_type ?? '') == 'other'){
                $name = $data->owner_name ?? '-';
                $relation = $data->owner_relation ?? '';

                return $relation
                    ? $name.' ('.$relation.')'
                    : $name;
            }

            // 👉 SENDIRI
            return $data->staff_owner_name ?? 'Sendiri';
        })
        ->addColumn('type', fn($data) => ($data->type ?? '-') == 'Lain lain' ? 'Lain-lain' : ($data->type ?? '-'))
        ->addColumn('description', fn($data) => $data->description ?? '-')
        ->addColumn('value', fn($data) => $data->value ? 'RM '.number_format($data->value,2) : '-')
        ->addColumn('financial_source', function($data){
    return !empty($data->financial_source)
        ? $data->financial_source
        : '-';
})
        ->addColumn('year', fn($data) => $data->year ?? '-')
        // ✅ DIKEMASKINI: tambah paparan nilai pelupusan RM
        ->addColumn('pelupusan', function($data){
            $method = trim((string)($data->disposal_method ?? ''));
            $date   = trim((string)($data->disposal_date ?? ''));
            $value  = $data->disposal_value ?? null;
            $status = $data->disposal_status ?? null;

if (!in_array($status, ['SUBMITTED', 'APPROVED'])) {
    return '-';
}

            if(
                ($method === '' || $method === '-') &&
                ($date === '' || $date === '-' || $date === '0000-00-00') &&
                empty($value)
            ){
                return '-';
            }

            $html = '';

            if($method !== '' && $method !== '-'){
                $html .= e($method);
            }

            if($date !== '' && $date !== '-' && $date !== '0000-00-00'){
                $html .= ($html ? '<br>' : '') . $date;
            }

            // ✅ TAMBAHAN: nilai pelupusan tidak wajib, papar hanya jika ada
            if(!empty($value)){
                $html .= ($html ? '<br>' : '') . '<strong>RM '.number_format($value, 2).'</strong>';
            }

            return $html ?: '-';
        })
        ->addColumn('terkini', function($data){
            if(($data->disposal_status ?? null) === 'SUBMITTED'){
                return '<span class="text-warning fw-bold">Menunggu Pelupusan</span>';
            }

            if(($data->disposal_status ?? null) === 'APPROVED'){
                return '<span class="text-danger fw-bold">Dilupuskan</span>';
            }

            $method = trim((string)($data->disposal_method ?? ''));
            $date   = trim((string)($data->disposal_date ?? ''));

            $hasOldDisposal = (
                $method !== '' &&
                $method !== '-' &&
                $date !== '' &&
                $date !== '-' &&
                $date !== '0000-00-00' &&
                empty($data->disposal_status)
            );

            if($hasOldDisposal){
                return '<span class="text-danger fw-bold">Dilupuskan</span>';
            }

            return '<span class="text-success fw-bold">Aktif</span>';
        })
        ->addColumn('declaration_status', function($data){
            return match($data->declaration_status ?? 'DRAFT') {
                'DRAFT' => '<span class="badge badge-light-secondary">Draf</span>',
                'SUBMITTED' => '<span class="badge badge-light-primary">Dihantar</span>',
                'RETURNED' => '<span class="badge badge-light-warning">Dikembalikan</span>',
                'APPROVED' => '<span class="badge badge-light-success">Disahkan</span>',
                default => '<span class="badge badge-light-secondary">Draf</span>',
            };
        })
        ->addColumn('declaration_status_raw', function($data){
            return $data->declaration_status ?: 'DRAFT';
        })
        ->addColumn('disposal_status_raw', function($data){
            return $data->disposal_status ?? null;
        })
        ->addColumn('action_state', function($data){
            $declaration = $data->declaration_status ?? 'DRAFT';
            $disposal = $data->disposal_status ?? null;

            $method = trim((string)($data->disposal_method ?? ''));
            $date   = trim((string)($data->disposal_date ?? ''));

            $hasOldDisposal = (
                $method !== '' &&
                $method !== '-' &&
                $date !== '' &&
                $date !== '-' &&
                $date !== '0000-00-00' &&
                empty($disposal)
            );

            if($disposal === 'SUBMITTED'){
                return 'APPROVE_DISPOSAL';
            }

            if($disposal === 'APPROVED' || $hasOldDisposal){
                return 'LOCKED';
            }

            if($declaration === 'SUBMITTED'){
                return 'LOCKED';
            }

            if($declaration === 'APPROVED'){
                return 'CAN_DISPOSE';
            }

            if($declaration === 'DRAFT' || $declaration === 'RETURNED'){
                return 'CAN_EDIT';
            }

            return 'LOCKED';
        })
        ->addColumn('action', function($data){

    $declaration = $data->declaration_status ?? 'DRAFT';
    $disposal = $data->disposal_status ?? null;

    $method = trim((string)($data->disposal_method ?? ''));
    $date   = trim((string)($data->disposal_date ?? ''));

    $hasOldDisposal = (
        $method !== '' &&
        $method !== '-' &&
        $date !== '' &&
        $date !== '-' &&
        $date !== '0000-00-00' &&
        empty($disposal)
    );

    // ✅ TAMBAHAN: semak user login sama ada admin atau bukan
    $isAdmin = Auth::user()->hasRole('admin');

    // ===============================
    // PELUPUSAN MENUNGGU PENGESAHAN
    // ===============================
    if($disposal === 'SUBMITTED'){

        // ❌ STAFF TAK BOLEH APPROVE
        if(!$isAdmin){
            return '<span class="badge badge-light-dark">Dikunci</span>';
        }

        // ✅ ADMIN SAHAJA
        return '
            <div class="d-flex gap-2 justify-content-center">

                <button class="btn btn-icon btn-sm btn-success harta-approve-disposal"
                    type="button"
                    title="Sahkan Pelupusan">

                    <i class="fas fa-check fs-4"></i>

                </button>

                <button class="btn btn-icon btn-sm btn-danger harta-reject-disposal"
                    type="button"
                    title="Tolak Pelupusan">

                    <i class="fas fa-times fs-4"></i>

                </button>

            </div>
        ';
    }

    // ===============================
    // DIKUNCI
    // ===============================
    if(
        $disposal === 'APPROVED' ||
        $hasOldDisposal ||
        $declaration === 'SUBMITTED'
    ){
        return '<span class="badge badge-light-dark">Dikunci</span>';
    }

    // ===============================
    // BOLEH MOHON PELUPUSAN
    // ===============================
    if($declaration === 'APPROVED'){

        return '
            <div class="dropdown">

                <button class="btn btn-icon btn-sm btn-primary"
                    type="button"
                    data-bs-toggle="dropdown">

                    <i class="fas fa-recycle fs-4"></i>

                </button>

                <ul class="dropdown-menu">

                    <li>
                        <button class="dropdown-item text-primary harta-pelupusan">
                            Mohon Pelupusan
                        </button>
                    </li>

                </ul>

            </div>
        ';
    }

    // ===============================
    // DRAFT / RETURNED
    // ===============================
    if(
        $declaration === 'DRAFT' ||
        $declaration === 'RETURNED'
    ){

        return '
            <div class="dropdown">

                <button class="btn btn-icon btn-sm btn-warning"
                    type="button"
                    data-bs-toggle="dropdown">

                    <i class="fas fa-pencil fs-4"></i>

                </button>

                <ul class="dropdown-menu">

                    <li>
                        <button class="dropdown-item text-warning harta-edit">
                            Kemaskini
                        </button>
                    </li>

                    <li>
                        <button class="dropdown-item text-danger harta-delete">
                            Padam
                        </button>
                    </li>

                </ul>

            </div>
        ';
    }

    return '<span class="badge badge-light-dark">Dikunci</span>';
})
        ->make();
}

public function storeUpdateHarta(Request $request)
{
    $m = $this->staffRepository->storeUpdateHarta($request);
    return $this->setDataResponse($m, !($m['status'] == 'error'));
}

public function storeHartaPelupusan(Request $request)
{
    $m = $this->staffRepository->storeHartaPelupusan($request);
    return $this->setDataResponse($m, !($m['status'] == 'error'));
}

public function submitHarta(Request $request)
{
    $staff_id = $request->staff_id;

    $count = StaffHarta::where('staff_id', $staff_id)
        ->whereIn('declaration_status', ['DRAFT', 'RETURNED'])
        ->count();

    if($count <= 0){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Tiada rekod harta berstatus draf untuk dihantar.',
        ], false);
    }

    StaffHarta::where('staff_id', $staff_id)
        ->whereIn('declaration_status', ['DRAFT', 'RETURNED'])
        ->update([
            'declaration_status' => 'SUBMITTED',
            'submitted_at' => now(),
            'returned_at' => null,
            'admin_remark' => null,
            'updated_at' => now(),
        ]);

   $senderUserId = Auth::id();

$admins = User::join('role_user', 'users.id', '=', 'role_user.user_id')
    ->join('roles', 'roles.id', '=', 'role_user.role_id')
    ->whereIn('roles.name', ['admin', 'super-admin'])
    ->select('users.*')
    ->distinct()
    ->get();

foreach ($admins as $admin) {
    NotificationHelper::send(
        $admin->id,
        'Pengisytiharan Harta Baharu',
        Auth::user()->name.' telah menghantar pengisytiharan harta untuk semakan.',
        route('staff.profile', [
            'user_id' => $senderUserId,
            'page' => 'harta'
        ]),
        'HARTA',
        'info'
    );
}

    return $this->setDataResponse([
        'status' => 'success',
        'message' => 'Perisytiharan harta berjaya dihantar kepada admin untuk semakan.',
    ]);
}
public function approveHarta(Request $request)
{
    $staff_id = $request->staff_id;

    $count = StaffHarta::where('staff_id', $staff_id)
        ->where('declaration_status', 'SUBMITTED')
        ->count();

    if($count <= 0){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Tiada perisytiharan berstatus dihantar untuk disahkan.',
        ], false);
    }

    StaffHarta::where('staff_id', $staff_id)
        ->where('declaration_status', 'SUBMITTED')
        ->update([
            'declaration_status' => 'APPROVED',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'admin_remark' => $request->admin_remark,
            'updated_at' => now(),
        ]);
        
        $staff = \App\Models\Staff::find($staff_id);

if ($staff && $staff->user_id) {
    NotificationHelper::send(
        $staff->user_id,
        'Pengisytiharan Harta Disahkan',
        'Pengisytiharan harta anda telah disahkan oleh admin.',
        route('staff.profile', [
            'user_id' => $staff->user_id,
            'page'    => 'harta'
        ]),
        'HARTA',
        'success'
    );
}

    return $this->setDataResponse([
        'status' => 'success',
        'message' => 'Perisytiharan harta berjaya disahkan.',
    ]);
}

public function returnHarta(Request $request)
{
    $request->validate([
        'admin_remark' => 'required|string|max:1000',
    ]);

    $staff_id = $request->staff_id;

    $count = StaffHarta::where('staff_id', $staff_id)
        ->where('declaration_status', 'SUBMITTED')
        ->count();

    if($count <= 0){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Tiada perisytiharan berstatus dihantar untuk dikembalikan.',
        ], false);
    }

    StaffHarta::where('staff_id', $staff_id)
        ->where('declaration_status', 'SUBMITTED')
        ->update([
            'declaration_status' => 'RETURNED',
            'returned_at' => now(),
            'admin_remark' => $request->admin_remark,
            'updated_at' => now(),
        ]);

        $staff = \App\Models\Staff::find($staff_id);

if ($staff && $staff->user_id) {
    NotificationHelper::send(
        $staff->user_id,
        'Pengisytiharan Harta Dikembalikan',
        'Pengisytiharan harta anda telah dikembalikan oleh admin untuk pembetulan.',
        route('staff.profile', [
            'user_id' => $staff->user_id,
            'page'    => 'harta'
        ]),
        'HARTA',
        'warning'
    );
}

    return $this->setDataResponse([
        'status' => 'success',
        'message' => 'Perisytiharan harta telah dikembalikan kepada staf untuk pembetulan.',
    ]);
}
public function approveHartaPelupusan(Request $request)
{
    $id = $request->id;

    $harta = StaffHarta::find($id);

    if(!$harta){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Rekod harta tidak dijumpai.',
        ], false);
    }

    if($harta->disposal_status !== 'SUBMITTED'){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Tiada permohonan pelupusan untuk disahkan.',
        ], false);
    }

    $harta->disposal_status = 'APPROVED';
    $harta->disposal_approved_at = now();
    $harta->disposal_approved_by = Auth::id();
    $harta->disposal_admin_remark = $request->disposal_admin_remark;
    $harta->save();

    // ✅ Notification kepada staff
$staff = \App\Models\Staff::find($harta->staff_id);

if ($staff && $staff->user_id) {
    NotificationHelper::send(
        $staff->user_id,
        'Pelupusan Harta Disahkan',
        'Permohonan pelupusan harta anda telah disahkan oleh admin.',
        route('staff.profile', [
            'user_id' => $staff->user_id,
            'page'    => 'harta'
        ]),
        'HARTA',
        'success'
    );
}

    return $this->setDataResponse([
        'status' => 'success',
        'message' => 'Pelupusan harta berjaya disahkan.',
    ]);
}

public function rejectHartaPelupusan(Request $request)
{
    $harta = StaffHarta::find($request->id);

    if(!$harta){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Rekod harta tidak dijumpai.',
        ], false);
    }

    if($harta->disposal_status !== 'SUBMITTED'){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Tiada permohonan pelupusan untuk ditolak.',
        ], false);
    }

    $harta->disposal_method = null;
    $harta->disposal_date = null;
    $harta->disposal_value = null;
    $harta->disposal_status = null;
    $harta->disposal_submitted_at = null;
    $harta->disposal_admin_remark = null;
    $harta->save();

    // ✅ Notification kepada staff
$staff = \App\Models\Staff::find($harta->staff_id);

if ($staff && $staff->user_id) {
    NotificationHelper::send(
        $staff->user_id,
        'Pelupusan Harta Ditolak',
        'Permohonan pelupusan harta anda telah ditolak oleh admin dan rekod harta kembali aktif.',
        route('staff.profile', [
            'user_id' => $staff->user_id,
            'page'    => 'harta'
        ]),
        'HARTA',
        'warning'
    );
}

    return $this->setDataResponse([
        'status' => 'success',
        'message' => 'Permohonan pelupusan telah ditolak dan rekod kembali aktif.',
    ]);
}

public function getHartaInfo(Request $request) : JsonResponse
{
    return $this->setDataResponse($this->staffRepository->getHarta($request->id));
}

public function deleteHarta(Request $request) : JsonResponse
{
    $harta = StaffHarta::find($request->id);

    if($harta && in_array($harta->declaration_status, ['SUBMITTED', 'APPROVED'])){
        return $this->setDataResponse([
            'status' => 'error',
            'message' => 'Rekod telah dihantar/disahkan dan tidak boleh dipadam.',
        ], false);
    }

    return $this->setResponse($this->setHardDelete(StaffHarta::class, $request->id, 'Harta'));
}
    // ================= OTHERS =================
    public function storeUpdateAppointed(Request $request)
    {
        $m = $this->staffRepository->setAppointedDate($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function storeUpdateWorkStatus(Request $request)
    {
        $m = $this->staffRepository->setWorkStatus($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function getPositionHistoryInfo(Request $request) : JsonResponse
    {
        return $this->setDataResponse($this->staffRepository->getPositionHistoryInfo($request->id));
    }

    public function storeUpdatePositionHistoryDate(Request $request)
    {
        $m = $this->staffRepository->storeUpdatePositionHistoryDate($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function setPositionAsActive(Request $request)
    {
        $m = $this->staffPositionRepository->setPositionAsActive($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }
}