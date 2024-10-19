<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Repositories\StaffRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
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
            $academicQualifications = $this->getAcademicQualifications();
            $bumiputera = $this->getBumiputeras();
            $religion = $this->getReligion();
            $salutation = $this->getSalutations();
            $gender = $this->getGenders();

            $responseData['country'] = $country;
            $responseData['state'] = $state;
            $responseData['race'] = $race;
            $responseData['marital_status'] = $maritalStatus;
            $responseData['academic_qualifications'] = $academicQualifications;
            $responseData['bumiputera'] = $bumiputera;
            $responseData['religion'] = $religion;
            $responseData['salutation'] = $salutation;
            $responseData['gender'] = $gender;
        }

        return view('staff.profile.index')->with($responseData);
    }

    public function storeUpdateMain(Request $request){
        $m = $this->staffRepository->storeUpdateProfile($request);
        return $this->setDataResponse($m, !($m['status'] == 'error'));
    }
}
