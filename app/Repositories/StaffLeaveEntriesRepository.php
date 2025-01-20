<?php

namespace App\Repositories;

use App\Models\LeaveCategory;
use App\Models\LeaveRequestStatus;
use App\Models\PublicHoliday;
use App\Models\StaffLeaveEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffLeaveEntriesRepository
{
    private StaffPositionRepository $staffPositionRepository;
    public function __construct(StaffPositionRepository $staffPositionRepository)
    {
        $this->staffPositionRepository = $staffPositionRepository;
    }

    public function getStaffLeaveEntry($id){
        return StaffLeaveEntry::with('getStaffPosition', 'getStaffLeave')->where('id', $id)->first();
    }

    public function storeNewRequest(Request $request){
        $staff_id = $request->staff_id;
        $leave_category = $request->leave_category;
        $leave_date_range = $request->leave_date_range;

        $getLeaveCategory = LeaveCategory::find($leave_category);
        $staffPosition = $this->staffPositionRepository->getStaffPosition($staff_id);
        $getBranchState = $staffPosition->getBranch->getState;
        $getWeekend = $getBranchState->getWeekendHoliday;

        $getRangeArr = explode(' to ', $leave_date_range);

        $date = [
            'start' => $getRangeArr[0],
            'end' => $getRangeArr[1] ?? $getRangeArr[0],
        ];

        $yearArr = [
            date('Y', strtotime($date['start'])),
            date('Y', strtotime($date['end']))
        ];

        $getPublic = PublicHoliday::whereIn('year', $yearArr)->where('state_id', $getBranchState->id)->where('h_date', '>=', $date['start'])->where('h_date', '<=', $date['end'])->get();

        $weekendH = [];

        if(count($getWeekend) > 0){
            foreach($getWeekend as $wee){
                $weekendH[] = $wee->getDay->name;
            }
        }

        $publicH = [];
        if(count($getPublic) > 0){
            foreach($getPublic as $p){
                $publicH[] = $p->h_date;
            }
        }

        $hTaken = 0;
        $currentDate = $date['start'];

        while ($currentDate <= $date['end']) {
            $needAdd = true;
            if(in_array(date('l', strtotime($currentDate)), $weekendH)){
                $needAdd = false;
            }

            if(in_array($currentDate, $publicH)){
                $needAdd = false;
            }

            if($needAdd){
                $hTaken++;
            }

            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        if($hTaken == 0){
            return [
                'status' => 'error',
                'message' => 'Tarikh Yang Anda Pilih Adalah Cuti Umum/Cuti Mingguan'
            ];
        }else{
            if($getLeaveCategory->is_half_day == true){
                $hTaken = 0.5 * $hTaken;
            }

            DB::beginTransaction();
            try{
                $m = new StaffLeaveEntry();
                $m->staff_position_id = $staffPosition->id;
                $m->staff_leave_id = $staffPosition->getStaffLeave->id;
                $m->leave_category_id = $leave_category;
                $m->start_date = $date['start'];
                $m->end_date = $date['end'];
                $m->days = $hTaken;
                $m->leave_request_status_id = LeaveRequestStatus::PENDING;
                $m->save();

                $sLeave = $staffPosition->getStaffLeave;
                $sLeave->leave_taken = $hTaken;
                $sLeave->leave_balance = $sLeave->leave_balance - $hTaken;
                $sLeave->save();

                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                return [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'status' => 'success',
            'message' => 'Permohonan Cuti Anda Sedang Menunggu Pengesahan'
        ];
    }

    public function getRequestListByUserId(Request $request){
        $user_id = $request->user_id;

        $search = $request->get('search');
        $searchStr = '';

        if($search){
            $searchStr = 'WHERE b.name LIKE ? OR s.name LIKE ?';
            $params = [
                '%'.$search.'%',
                '%'.$search.'%',
            ];
        }else{
            $params = [
            ];
        }

        $m = DB::select('
            SELECT
            sle.id,
            sle.start_date,
            sle.end_date,
            sle.days,
            lrs.name as l_status
            FROM staff_leave_entries sle
            JOIN staff_positions sp ON sp.id = sle.staff_position_id
            JOIN staffs s ON s.id = sp.staff_id
            AND s.user_id = '.$user_id.'
            JOIN leave_request_statuses lrs ON lrs.id = sle.leave_request_status_id
            '.$searchStr.'
        ', $params);

        return $m;
    }

    public function deleteRequest(Request $request){
        $id = $request->id;
        DB::beginTransaction();
        try{
            $entry = $this->getStaffLeaveEntry($id);
            $sLeave = $entry->getStaffLeave;
            $sLeave->leave_balance = $sLeave->leave_balance + $entry->days;
            $sLeave->leave_taken = $sLeave->leave_taken - $entry->days;
            $sLeave->save();
            DB::commit();
            return true;
        }catch (\Exception $e){
            DB::rollBack();
            return false;
        }
    }
}
