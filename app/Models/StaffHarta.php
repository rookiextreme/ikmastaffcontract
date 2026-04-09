<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffHarta extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'type',
        'description',
        'value',
        'year',
    ];
}