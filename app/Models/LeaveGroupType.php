<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveGroupType extends Model
{
    protected $table = 'leave_group_types';

    protected $fillable = ['name', 'sort', 'is_active'];
}
