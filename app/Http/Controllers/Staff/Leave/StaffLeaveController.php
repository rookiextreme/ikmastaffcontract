<?php

namespace App\Http\Controllers\Staff\Leave;

use App\Http\Controllers\Controller;
use App\Library\Datatable\SymTable;
use App\Models\StaffLeaveEntry;
use App\Repositories\StaffLeaveEntriesRepository;
use App\Repositories\StaffRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
use Illuminate\Http\Request;

class StaffLeaveController extends Controller
{
    use CommonTrait, LookupTrait;
    private StaffRepository $staffRepository;
    private StaffLeaveEntriesRepository $staffLeaveEntriesRepository;

    public function __construct(StaffRepository $staffRepository, StaffLeaveEntriesRepository $staffLeaveEntriesRepository){
        $this->staffRepository = $staffRepository;
        $this->staffLeaveEntriesRepository = $staffLeaveEntriesRepository;
    }

    public function leaveNewRequest($user_id){
        $staff = $this->staffRepository->getStaffProfile($user_id);
        $leaveCategory = $this->getLeaveCategories();
        return view('staff.leave.new-request', [
            'staff' => $staff,
            'leaveCategory' => $leaveCategory
        ]);
    }

    public function storeUpdateNewRequest(Request $request){
        $m = $this->staffLeaveEntriesRepository->storeNewRequest($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function leaveRequest($user_id){
        return view('staff.leave.request', [
            'user_id' => $user_id
        ]);
    }

    public function requestList(Request $request){
        $entries = $this->staffLeaveEntriesRepository->getRequestListByUserId($request);

        return SymTable::of($entries)
            ->addRowAttr([
                'data-id' => function($data){
                    return $data->id;
                }
            ])
            ->addColumn('start', function($data){
                return $this->regularDate($data->start_date);
            })->addColumn('end', function($data){
                return $this->regularDate($data->end_date);
            })->addColumn('days', function($data){
                return $data->days;
            })->addColumn('status', function($data){
                return strtoupper($data->l_status);
            })->make();
    }

    public function requestDelete(Request $request){
       $m = $this->staffLeaveEntriesRepository->deleteRequest($request);

       if($m){
           return $this->setResponse($this->setHardDelete(StaffLeaveEntry::class, $request->id, 'Permohonan'));
       }else{
           return $this->setResponse('WHOOPS', false);
       }
    }
}
