<?php

namespace App\Repositories;

use App\Models\Staff;
use App\Models\User;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffRepository
{
    use CommonTrait;
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    public function setBasicStaffProfile(User $user, Request $request){
        $m = new Staff;
        $m->user_id = $user->id;
        $m->phone_no = $request->mobile_phone;
        $m->save();
    }

    public function getStaffProfile($user_id){
        return Staff::with('getUser', 'getBumiputera', 'getCountry', 'getState', 'getGender', 'getRace', 'getReligion', 'getSalutation')->where('user_id', $user_id)->first();
    }

    public function storeUpdateProfile(Request $request){
        $name = $request->name;
        $identification_no = $request->identification_no;
        $address = $request->address;
        $email = $request->email;
        $city = $request->city;
        $postal_code = $request->postal_code;
        $country = $request->country;
        $state = $request->state;
        $mobile_phone = $request->mobile_phone;
        $marital = $request->marital;
        $race = $request->race;
        $race_other = $request->race_other;
        $bumiputera = $request->bumiputera;
        $bumiputera_other = $request->bumiputera_other;
        $dob = $request->dob;
        $birth_country = $request->birth_country;
        $birth_certificate = $request->birth_certificate;
        $birth_state = $request->birth_state;
        $gender = $request->gender;
        $salutation = $request->salutation;
        $religion = $request->religion;

        $staff_id = $request->staff_id;

        DB::beginTransaction();
        try{
            $staff = Staff::find($staff_id);
            $staff->address = $address;
            $staff->city = $city;
            $staff->postal_code = $postal_code;
            $staff->race_id = $race;
            $staff->other_race = $race_other;
            $staff->country_id = $country;
            $staff->state_id = $state;
            $staff->phone_no = $mobile_phone;
            $staff->marital_status_id = $marital;
            $staff->bumiputera_id = $bumiputera;
            $staff->bumiputera_other = $bumiputera_other;
            $staff->dob = $dob ? $this->reverseDate($dob) : null;
            $staff->birth_country_id = $birth_country;
            $staff->birth_state_id = $birth_state;
            $staff->birth_certificate_no = $birth_certificate;
            $staff->gender_id = $gender;
            $staff->salutation_id = $salutation;
            $staff->religion_id = $religion;

            $existUser = $this->userRepository->checkExist($email, $identification_no, $staff->user_id);
            if($existUser['status'] == 'exist'){
                return [
                    'status' => 'error',
                    'message' => 'Pengguna Sudah Wujud'
                ];
            }

            $staff->profile_complete = 1;
            $staff->save();
            $existUser['user']->ic_no = $identification_no;
            $existUser['user']->save();
            $this->userRepository->storeUser($existUser['user'], $name, $email, 'staff', true);

            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Rekod Peribadi Dikemaskini',
            'url' => route('staff.profile', ['user_id' => $staff->user_id, 'page' => 'main'])
        ];
    }
}
