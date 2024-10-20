<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Library\Datatable\SymTable;
use App\Models\StaffAcademic;
use App\Repositories\StaffRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use CommonTrait, LookupTrait;
    private StaffRepository $staffRepository;

    public function __construct(StaffRepository $staffRepository){
        $this->staffRepository = $staffRepository;

    }
    public function index($user_id, $page){
        $staff = $this->staffRepository->getStaffProfile($user_id);

        $responseData = [
            'page' => $page,
            'user_id' => $user_id,
            'staff' => $staff
        ];

        if($page == 'main'){
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
        }

        return view('staff.profile.index')->with($responseData);
    }

    public function storeUpdateMain(Request $request){
        $m = $this->staffRepository->storeUpdateProfile($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }

    public function academicList(Request $request){
        $model = $this->staffRepository->getAcademicList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => function($data){
                    return $data->id;
                }
            ])
            ->addColumn('institution', function($data){
                return $data->institution_name;
            })
            ->addColumn('certificate', function($data){
                return $data->certificate_name;
            })
            ->addColumn('specialization', function($data){
                return '<span class="text-primary">'.ucwords($data->major_specialization).'</span>'.($data->minor_specialization ? '<br><span class="text-info">'.ucwords($data->minor_specialization).'</span>' : '');
            })
            ->addColumn('grade', function($data){
                return $data->overall_grade ?? '-';
            })->make();
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
}
