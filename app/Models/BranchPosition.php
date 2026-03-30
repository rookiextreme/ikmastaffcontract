<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchPosition extends Model
{
    public function getPosition()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function getGrade()
    {
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }

    public function getUnit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
