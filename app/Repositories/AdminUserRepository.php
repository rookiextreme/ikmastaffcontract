<?php

namespace App\Repositories;

use App\Jobs\UserJob;
use App\Models\ParticipantProfile;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserRepository
{
    private UserRepository $userRepository;
    private StaffRepository $staffRepository;

    public function __construct(UserRepository $userRepository, StaffRepository $staffRepository)
    {
        $this->userRepository = $userRepository;
        $this->staffRepository = $staffRepository;
    }

    public function getUserList(Request $request){
        $search = $request->get('search');
        $searchStr = '';

        $user = Auth::user();
        $rolesDrop = [];
        if($user->hasRole('super-admin')){
            $rolesDrop = [2,3,4];
        }else if($user->hasRole('admin')){
            $rolesDrop = [3, 4];
        }

        if($search){
            $searchStr = 'WHERE u.name LIKE ? OR u.email LIKE ? OR r.display_name LIKE ?';
            $params = [
                $search.'%',
                $search.'%',
                $search.'%',
            ];
        }else{
            $params = [
            ];
        }

        $m = DB::select('
            SELECT
            u.id,
            u.ic_no,
            u.name,
            u.email,
            u.active,
            IF(u.active = 0, "Tidak Aktif", "Aktif") as active_display,
            r.display_name,
            r.name as role_name,
            r.id as role_id
            FROM users u
            JOIN role_user ru ON ru.user_id = u.id
            JOIN roles r ON r.id = ru.role_id
            '.(!empty($rolesDrop) ? 'AND r.id IN (' . implode(',', $rolesDrop) . ')' : '').'
            '.$searchStr.'
        ', $params);

        return $m;
    }

    public function adminStoreUser(Request $request){
        $name = $request->name;
        $email = $request->email;
        $role = $request->role;
        $id = $request->id;
        $identification_no = $request->identification_no;

        $check = $this->userRepository->checkExist($email, $identification_no, $id);
        DB::beginTransaction();
        try{
            if($check['status'] == 'exist'){
                return [
                    'status' => 'error',
                    'message' => 'Pengguna Sudah Wujud'
                ];
            }else if($check['status'] == 'update' || $check['status'] == 'new'){
                $newHashed = null;
                $this->userRepository->storeUser($check['user'], $name, $email, $role, $check['status'] == 'update');
                $check['user']->syncRoles([$role]);
                $check['user']->ic_no = $identification_no;
                if($check['status'] == 'new'){
                    $newHashed = Str::random(10);
                    $check['user']->password = Hash::make($newHashed);
                }
                $check['user']->save();
                if($role == 4){//peserta
                    $checkStaff = Staff::where('user_id', $check['user']->id)->first();

                    if(!$checkStaff){
                        $request->request->add(['mobile_phone' => null]);
                        $this->staffRepository->setBasicStaffProfile($check['user'], $request);
                    }else{
                        $checkStaff->deleted = false;
                    }

                    if($check['status'] == 'new'){
                        dispatch(new UserJob($check['user']->id, 'admin_add_new_user', $newHashed));
                    }
                }else{
                    //Applies for admin, approval-admin
                    if($check['status'] == 'new'){
                        dispatch(new UserJob($check['user']->id, 'admin_add_new_user', $newHashed));
                    }
                }
                DB::commit();
            }
        }catch (\Exception $e){
            DB::rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Pengguna berjaya '.($check['status'] == 'new' ? 'ditambah' : 'dikemaskini')
        ];
    }

    public function getUser($user_id){
        $m = DB::select('
            SELECT
            u.id,
            u.name,
            u.email,
            ru.role_id,
            u.ic_no
            FROM users u
            JOIN role_user ru ON ru.user_id = u.id
            JOIN roles r ON ru.role_id = r.id
            WHERE u.id = ?
        ', [
            $user_id
        ]);

        return $m[0];
    }

    public function activateUser(Request $request){
        $active = $request->active;
        $id = $request->id;

        DB::beginTransaction();
        try{
            $m = User::find($id);
            $m->active = $active == 0;
            $m->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Pengguna '.($active == 0 ? 'Diaktifkan' : 'Dinyahaktifkan')
        ];
    }
}
