<?php

namespace App\Repositories;

use App\Models\BranchPosition;
use App\Models\StaffPosition;
use App\Models\StaffPositionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPositionRepository
{
    public function checkExistRecord($staff_id){
        $m = StaffPosition::where('staff_id',$staff_id)->first();
        if(!$m){
            $m = new StaffPosition();
            $m->staff_id = $staff_id;
            $m->save();
        }

        return $m;
    }

    public function storeUpdatePosition(Request $request){
        $staff_id = $request->staff_id;
        $branch_select = $request->branch_select;
        $position_select = $request->position_select;
        $position_start_date = $request->position_start_date;

        DB::beginTransaction();
        try{
            $m = $this->getStaffPosition($staff_id);
            $m->branch_position_id = $position_select;
            $m->branch_id = $branch_select;
            $m->save();

            $branchPosition = BranchPosition::find($position_select);

            $sLeave = $m->getStaffLeave;
            $sLeave->staff_position_id = $m->id;
            $sLeave->leave_total = $branchPosition->default_holiday;
            $sLeave->leave_balance = $branchPosition->default_holiday;
            $sLeave->mc_total = 14;
            $sLeave->mc_balance = 14;
            $sLeave->save();

            $this->storeUpdateToHistory($m, $position_start_date);

            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Jawatan Berjaya Dikemaskini'
        ];
    }

    public function getStaffPosition($staff_id){
        return StaffPosition::with('getStaff', 'getStaffLeave', 'getStaffLeaveEntries')->where('staff_id', $staff_id)->first();
    }

    public function storeUpdateToHistory(StaffPosition $sp, $start_date = null){
        $checkHistory = new StaffPositionHistory();
        $checkHistory->staff_id = $sp->staff_id;
        $checkHistory->branch_position_id = $sp->branch_position_id;
        $checkHistory->branch_id = $sp->branch_id;
        $checkHistory->start_date = $start_date ? date('Y-m-d', strtotime($start_date)) : null;
        $checkHistory->active = true;
        $checkHistory->save();
    }

    public function setPositionAsActive(Request $request){
        $id = $request->id;

        $m = StaffPositionHistory::find($id);
        $m->active = true;
        $m->save();

        $getOtherPosition = StaffPositionHistory::where('staff_id', $m->staff_id)->where('id', '!=', $m->id)->get();

        if(count($getOtherPosition) > 0){
            foreach($getOtherPosition as $otherPosition){
                $otherPosition->active = false;
                $otherPosition->save();
            }
        }

        $staffPosition = StaffPosition::where('staff_id', $m->staff_id)->first();
        $staffPosition->branch_position_id = $m->branch_position_id;
        $staffPosition->branch_id = $m->branch_id;
        $staffPosition->save();

        return [
            'status' => 'success',
            'message' => 'Jawatan Ditetapkan Sebagai Aktif'
        ];
    }

    public function getStaffByRequest(Request $request){
        $staff_name = $request->staff_name;
        $branch = $request->branch;
        $grade = $request->grade;
        $year_start = $request->year_start;
        $year_end = $request->year_end;

        $paramsList = [];

        if($branch){
            $paramsList[] = $branch;
        }
        if($grade){
            $paramsList[] = $grade;
        }
        if($year_start){
            $paramsList[] = $year_start;
        }
        if($year_end){
            $paramsList[] = $year_end;
        }

        if($staff_name){
            $paramsList[] = '%'.$staff_name.'%';
        }

        $db = DB::select('
            SELECT
            u.name,
            g.name as grade,
            p.name as position,
            b.name as branch_name,
            sph.start_date,
            sph.end_date
            FROM staff_position_histories sph
            JOIN branches b ON b.id = sph.branch_id
            JOIN branch_positions bp ON bp.id = sph.branch_position_id
            JOIN staffs s ON s.id = sph.staff_id
            JOIN users u ON u.id = s.user_id
            JOIN grades g ON g.id = bp.grade_id
            JOIN positions p ON p.id = bp.position_id
            '.($branch ? 'AND b.id = ?' : '').'
            '.($grade ? 'AND bp.grade_id = ?' : '').'
            '.($year_start ? 'AND YEAR(sph.start_date) >= ?' : '').'
            '.($year_end ? 'AND YEAR(sph.end_date) <= ?' : '').'
            '.($staff_name ? 'AND u.name LIKE ?' : '').'
        ', $paramsList);

        return $db;
    }
}
