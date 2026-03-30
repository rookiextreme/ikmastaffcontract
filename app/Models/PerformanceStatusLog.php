<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceStatusLog extends Model
{
    protected $fillable = [
        'evaluation_id',
        'actor_id',
        'actor_role',
        'action',
        'from_status',
        'to_status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function evaluation()
    {
        return $this->belongsTo(PerformanceEvaluation::class, 'evaluation_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
