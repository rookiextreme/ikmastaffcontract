<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Library\Datatable\SymTable;
use App\Models\BranchPosition;
use App\Models\StaffAcademic;
use App\Models\StaffFamily;
use App\Models\StaffHarta; // ✅ TAMBAH
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

class StaffController extends Controller
{
    use CommonTrait, LookupTrait;
    private StaffRepository $staffRepository;
    private BranchRepository $branchRepository;
    private BranchPositionRepository $branchPositionRepository;
    private StaffPositionRepository $staffPositionRepository;
    private StaffLeaveRepository $staffLeaveRepository;

    public function __construct(StaffRepository $staffRepository, BranchRepository $branchRepository, BranchPositionRepository $branchPositionRepository, StaffPositionRepository $staffPositionRepository, StaffLeaveRepository $staffLeaveRepository){
        $this->staffRepository = $staffRepository;
        $this->branchRepository = $branchRepository;
        $this->branchPositionRepository = $branchPositionRepository;
        $this->staffPositionRepository = $staffPositionRepository;
        $this->staffLeaveRepository = $staffLeaveRepository;
    }

    public function index($user_id, $page, Request $request){
        $staff = $this->staffRepository->getStaffProfile($user_id);

        $responseData = [
            'page' => $page,
            'user_id' => $user_id,
            'staff' => $staff
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

    // ================= ACADEMIC =================
    public function academicList(Request $request){
        $model = $this->staffRepository->getAcademicList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => fn($data) => $data->id
            ])
            ->addColumn('level', fn($data) => $data->qualification)
            ->addColumn('institution', fn($data) => $data->institution_name)
            ->addColumn('certificate', function($data){
                $pro = $data->certification_professional ? '<a class="text-warning" target="_blank" href="'.asset('uploads/staff/academics/cert_pro/'.$data->certification_professional).'">Papar Sijil</a>' : '';
                $cert = $data->certificate_file ? '<br><a target="_blank" href="'.asset('uploads/staff/academics/cert/'.$data->certificate_file).'">Papar Sijil</a>' : '';
                return $pro.$cert ?: '-';
            })
            ->addColumn('specialization', function($data){
                return '<span class="text-primary">'.ucwords($data->major_specialization).'</span>'.($data->minor_specialization ? '<br><span class="text-info">'.ucwords($data->minor_specialization).'</span>' : '');
            })
            ->addColumn('grade', fn($data) => $data->overall_grade ?? '-')
            ->make();
    }

    public function storeUpdateAcademic(Request $request){
        $m = $this->staffRepository->storeUpdateAcademic($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function getAcademicInfo(Request $request) : JsonResponse{
        return $this->setDataResponse($this->staffRepository->getAcademic($request->id));
    }

    public function deleteAcademic(Request $request) : JsonResponse{
        return $this->setResponse($this->setHardDelete(StaffAcademic::class, $request->id, 'Akademik'));
    }

    // ================= FAMILY =================
    public function familyList(Request $request){
        $model = $this->staffRepository->getFamilyList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => fn($data) => $data->id
            ])
            ->addColumn('name', fn($data) => $data->name.'<br>Umur '.(Carbon::parse($data->dob)->age))
            ->addColumn('email', fn($data) => $data->email.'<br>'.$data->phone)
            ->addColumn('relation', fn($data) => $data->relation)
            ->addColumn('grade', fn($data) => $data->overall_grade ?? '-')
            ->addColumn('death', fn($data) => $data->death_date ? $this->regularDate($data->death_date) : '-')
            ->make();
    }

    public function storeUpdateFamily(Request $request){
        $m = $this->staffRepository->storeUpdateFamily($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function getFamilyInfo(Request $request) : JsonResponse{
        return $this->setDataResponse($this->staffRepository->getFamily($request->id));
    }

    public function deleteFamily(Request $request) : JsonResponse{
        return $this->setResponse($this->setHardDelete(StaffFamily::class, $request->id, 'Maklumat Keluarga'));
    }

    // ================= HARTA (BARU) =================
  public function hartaList(Request $request){
    $model = $this->staffRepository->getHartaList($request);

    return SymTable::of($model)
        ->addRowAttr([
            'data-id' => fn($data) => $data->id
        ])
        ->addColumn('type', fn($data) => ($data->type ?? '-') == 'Lain lain' ? 'Lain-lain' : ($data->type ?? '-'))
        ->addColumn('description', fn($data) => $data->description ?? '-')
        ->addColumn('value', fn($data) => $data->value ? 'RM '.number_format($data->value,2) : '-')
        ->addColumn('year', fn($data) => $data->year ?? '-')
        ->make();
}

    public function storeUpdateHarta(Request $request){
        $m = $this->staffRepository->storeUpdateHarta($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function getHartaInfo(Request $request) : JsonResponse{
        return $this->setDataResponse($this->staffRepository->getHarta($request->id));
    }

    public function deleteHarta(Request $request) : JsonResponse{
        return $this->setResponse($this->setHardDelete(StaffHarta::class, $request->id, 'Harta'));
    }

    // ================= OTHERS =================
    public function storeUpdateAppointed(Request $request){
        $m = $this->staffRepository->setAppointedDate($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function storeUpdateWorkStatus(Request $request){
        $m = $this->staffRepository->setWorkStatus($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function getPositionHistoryInfo(Request $request) : JsonResponse{
        return $this->setDataResponse($this->staffRepository->getPositionHistoryInfo($request->id));
    }

    public function storeUpdatePositionHistoryDate(Request $request){
        $m = $this->staffRepository->storeUpdatePositionHistoryDate($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function setPositionAsActive(Request $request){
        $m = $this->staffPositionRepository->setPositionAsActive($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }
}