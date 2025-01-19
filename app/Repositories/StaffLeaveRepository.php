<?php

namespace App\Repositories;

use App\Models\StaffLeave;

class StaffLeaveRepository
{
    public function checkExistRecord($staff_position_id){
        $m = StaffLeave::where('staff_position_id',$staff_position_id)->first();
        if(!$m){
            $m = new StaffLeave();
            $m->staff_position_id = $staff_position_id;
            $m->save();
        }

        return $m;
    }
}
