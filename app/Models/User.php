<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\Staff;
use App\Models\StaffPosition;

class User extends Authenticatable implements LaratrustUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'identification_no',
        'no_staff'          // ✅ tambah No. Kakitangan
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getIdentificationNoAttribute()
    {
        return $this->attributes['ic_no'];
    }

    // Mutator for 'identification_no'
    public function setIdentificationNoAttribute($value)
    {
        $this->attributes['ic_no'] = $value;
    }

    // ✅ KEKAL
    public function getStaff(){
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    // ✅ TAMBAH (alias standard supaya boleh panggil $user->staff)
    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id', 'id');
    }

    /**
     * ✅ TAMBAH (posisi terkini staff -> staff_positions)
     *
     * DB awak:
     * - staffs.user_id = users.id
     * - staff_positions.staff_id = staffs.id
     *
     * Jadi relation kena lalu Staff dulu (hasOneThrough).
     */
    public function staffPosition()
    {
        return $this->hasOneThrough(
            StaffPosition::class,
            Staff::class,
            'user_id',  // staffs.user_id -> users.id
            'staff_id', // staff_positions.staff_id -> staffs.id
            'id',       // users.id
            'id'        // staffs.id
        );
    }
}