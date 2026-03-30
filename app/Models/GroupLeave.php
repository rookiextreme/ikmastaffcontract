<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupLeave extends Model
{
    protected $fillable = [
        'staff_id',
        'applied_by',
        'start_date',
        'end_date',
        'total_days',
        'remarks',
        'attachment_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
