<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffHarta extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_SUBMITTED = 'SUBMITTED';
    public const STATUS_RETURNED = 'RETURNED';
    public const STATUS_APPROVED = 'APPROVED';

    protected $fillable = [
        'staff_id',
        'type',
        'description',
        'value',
        'year',
        'financial_source',
        'attachment',

        // Pemilik harta
        'owner_type',
        'family_id',
        'owner_name',
        'owner_relation',

        // Pelupusan
        'disposal_method',
        'disposal_date',
        'disposal_value', 
        'disposal_status',
        'disposal_submitted_at',
        'disposal_approved_at',
        'disposal_approved_by',
        'disposal_admin_remark',

        // Status perisytiharan
        'declaration_status',
        'submitted_at',
        'returned_at',
        'approved_at',
        'approved_by',
        'admin_remark',
    ];

    public function isLocked(): bool
    {
        return in_array($this->declaration_status, [
            self::STATUS_SUBMITTED,
            self::STATUS_APPROVED,
        ]);
    }
}