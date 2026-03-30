<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequestStatus extends Model
{
    const PENDING = 1;
    const APPROVED = 2;
    const REJECTED = 3;
     const CANCELLED = 4; // ✅ tambah kalau guna status batal
}
