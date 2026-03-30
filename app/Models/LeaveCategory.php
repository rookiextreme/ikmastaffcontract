<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveCategory extends Model
{
    protected $table = 'leave_categories';

    protected $fillable = [
        'name',
        'is_full_day',
        'is_half_day',
        'is_mc',
        'is_group_leave',
    ];
}
