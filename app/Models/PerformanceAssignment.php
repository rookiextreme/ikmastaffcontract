<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PerformanceAssignment extends Model
{
    protected $fillable = [
        'performance_period_id','pyd_user_id','ppp_user_id','ppk_user_id','unit_id','branch_id'
    ];

    public function period()
    {
        return $this->belongsTo(PerformancePeriod::class, 'performance_period_id');
    }

    public function pydUser()
    {
        return $this->belongsTo(User::class, 'pyd_user_id');
    }

    public function pppUser()
    {
        return $this->belongsTo(User::class, 'ppp_user_id');
    }

    public function ppkUser()
    {
        return $this->belongsTo(User::class, 'ppk_user_id');
    }

    public function evaluation()
    {
        return $this->hasOne(PerformanceEvaluation::class, 'assignment_id');
    }
}
