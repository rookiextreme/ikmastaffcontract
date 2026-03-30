<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffLeaveEntry;
use App\Models\Branch;
use App\Models\BranchPosition;
use App\Models\Position;
use App\Models\Grade;

class StaffPosition extends Model
{
    public function getStaff(){
        return $this->hasOne(Staff::class,'id','staff_id');
    }

    public function getStaffLeave(){
        return $this->hasOne(StaffLeave::class,'staff_position_id','id');
    }

    public function getStaffLeaveEntries(){
        return $this->hasMany(StaffLeaveEntry::class,'staff_position_id','id');
    }

    public function getBranch(){
        return $this->hasOne(Branch::class,'id','branch_id');
    }

    public function getBranchPosition(){
        return $this->hasOne(BranchPosition::class,'id','branch_position_id');
    }

    // =========================
    // ✅ TAMBAH (ikut struktur DB sebenar)
    // staff_positions -> branch_positions -> positions/grades
    // =========================

    // Standard Eloquent (senang eager load)
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function branchPosition()
    {
        return $this->belongsTo(BranchPosition::class, 'branch_position_id', 'id');
    }

    // ✅ Jawatan melalui branch_positions.position_id
    public function position()
    {
        return $this->hasOneThrough(
            Position::class,
            BranchPosition::class,
            'id',            // branch_positions.id
            'id',            // positions.id
            'branch_position_id', // staff_positions.branch_position_id
            'position_id'    // branch_positions.position_id
        );
    }

    // ✅ Gred melalui branch_positions.grade_id
    public function grade()
    {
        return $this->hasOneThrough(
            Grade::class,
            BranchPosition::class,
            'id',
            'id',
            'branch_position_id',
            'grade_id'
        );
    }
}