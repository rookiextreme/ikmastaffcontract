<?php

namespace App\Repositories;

use App\Jobs\StaffLeaveJob;
use App\Models\LeaveCategory;
use App\Models\LeaveRequestStatus;
use App\Models\PublicHoliday;
use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffLeaveEntry;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffLeaveEntriesRepository
{
    use CommonTrait;

    private StaffPositionRepository $staffPositionRepository;
    private StaffLeaveRepository $staffLeaveRepository;

    public function __construct(
        StaffPositionRepository $staffPositionRepository,
        StaffLeaveRepository $staffLeaveRepository
    ) {
        $this->staffPositionRepository = $staffPositionRepository;
        $this->staffLeaveRepository = $staffLeaveRepository;
    }

    public function getStaffLeaveEntry($id)
    {
        return StaffLeaveEntry::with('getStaffPosition', 'getStaffLeave', 'getLeaveCategory')
            ->where('id', $id)
            ->first();
    }

    public function storeNewRequest(Request $request)
    {
        $staff_id          = $request->staff_id;
        $leave_category    = $request->leave_category;
        $leave_date_range  = $request->leave_date_range;
        $leave_approver    = $request->leave_approver;
        $leave_reason      = $request->leave_reason;
        $leave_start_time  = $request->leave_start_time;
        $leave_end_time    = $request->leave_end_time;

        // ✅ TAMBAH: jenis cuti kelompok
        $leave_group_type_id = $request->leave_group_type_id;

        $getLeaveCategory  = LeaveCategory::find($leave_category);
        $staffPosition     = $this->staffPositionRepository->getStaffPosition($staff_id);
        $getBranchState    = $staffPosition->getBranch->getState;
        $getWeekend        = $getBranchState->getWeekendHoliday;
        $leave_mc          = $request->file('leave_mc');

        // ✅ validasi ringkas untuk cuti kelompok
        if (($getLeaveCategory->is_group_leave ?? 0) == 1 && empty($leave_group_type_id)) {
            return [
                'status'  => 'error',
                'message' => 'Sila pilih Jenis Cuti Kelompok'
            ];
        }

        if ($getLeaveCategory->is_half_day) {
            $start = \DateTime::createFromFormat('H:i', $leave_start_time);
            $end   = \DateTime::createFromFormat('H:i', $leave_end_time);

            if ($end < $start) {
                $end->modify('+1 day');
            }

            $interval = $start->diff($end);
            $hours    = $interval->h;

            if ($hours > 4) {
                return [
                    'status'  => 'error',
                    'message' => 'Masa Tidak Boleh Melebihi 4 Jam'
                ];
            }
        }

        $getRangeArr = explode(' to ', $leave_date_range);

        $date = [
            'start' => $getRangeArr[0],
            'end'   => $getRangeArr[1] ?? $getRangeArr[0],
        ];

        $yearArr = [
            date('Y', strtotime($date['start'])),
            date('Y', strtotime($date['end']))
        ];

        $getPublic = PublicHoliday::whereIn('year', $yearArr)
            ->where('state_id', $getBranchState->id)
            ->where('h_date', '>=', $date['start'])
            ->where('h_date', '<=', $date['end'])
            ->get();

        $weekendH = [];
        if (count($getWeekend) > 0) {
            foreach ($getWeekend as $wee) {
                $weekendH[] = $wee->getDay->name;
            }
        }

        $publicH = [];
        if (count($getPublic) > 0) {
            foreach ($getPublic as $p) {
                $publicH[] = $p->h_date;
            }
        }

        $hTaken      = 0;
        $currentDate = $date['start'];

        while ($currentDate <= $date['end']) {
            $needAdd = true;

            if (in_array(date('l', strtotime($currentDate)), $weekendH)) {
                $needAdd = false;
            }

            if (in_array($currentDate, $publicH)) {
                $needAdd = false;
            }

            if ($needAdd) {
                $hTaken++;
            }

            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }

        if ($hTaken == 0) {
            return [
                'status'  => 'error',
                'message' => 'Tarikh Yang Anda Pilih Adalah Cuti Umum/Cuti Mingguan'
            ];
        }

        if ($getLeaveCategory->is_half_day == true) {
            $hTaken = 0.5 * $hTaken;
        }

        DB::beginTransaction();
        try {
            // ✅ pastikan rekod staff_leave wujud
            $sLeave = $staffPosition->getStaffLeave
                ?: $this->staffLeaveRepository->checkExistRecord($staffPosition->id);

            $m = new StaffLeaveEntry();
            $m->staff_position_id = $staffPosition->id;
            $m->staff_leave_id = $sLeave->id;
            $m->approver_id = $leave_approver;
            $m->leave_category_id = $leave_category;

            // ✅ simpan jenis cuti kelompok jika kategori group
            $m->leave_group_type_id = (($getLeaveCategory->is_group_leave ?? 0) == 1)
                ? $leave_group_type_id
                : null;

            $m->start_date = $date['start'];
            $m->end_date = $date['end'];

            if ($getLeaveCategory->is_half_day == true) {
                $m->start_time = $leave_start_time;
                $m->end_time   = $leave_end_time;
            }

            $m->days = $hTaken;
            $m->leave_request_status_id = LeaveRequestStatus::PENDING;
            $m->reason = $leave_reason;

            if ($leave_mc) {
                $up = $this->uploadImage($leave_mc, public_path('uploads/staff/mc'));
                $m->mc_upload = $up;
            }

            $m->save();

            // ===================== KIRA & TOLAK BAKI CUTI =====================
            if ($getLeaveCategory->is_mc == true) {

                $sLeave->mc_taken   = $sLeave->mc_taken + $hTaken;
                $sLeave->mc_balance = $sLeave->mc_balance - $hTaken;

            } elseif ($getLeaveCategory->is_group_leave == true) {

                if ($sLeave->group_balance < $hTaken) {
                    DB::rollBack();
                    return [
                        'status'  => 'error',
                        'message' => 'Baki Cuti Kelompok tidak mencukupi'
                    ];
                }

                $sLeave->group_taken   = $sLeave->group_taken + $hTaken;
                $sLeave->group_balance = $sLeave->group_balance - $hTaken;

            } elseif ($getLeaveCategory->is_half_day == true) {

                if ($sLeave->leave_balance < $hTaken) {
                    DB::rollBack();
                    return [
                        'status'  => 'error',
                        'message' => 'Baki cuti tidak mencukupi'
                    ];
                }

                $sLeave->leave_taken   = $sLeave->leave_taken + $hTaken;
                $sLeave->leave_balance = $sLeave->leave_balance - $hTaken;

            } else {

                if ($sLeave->leave_balance < $hTaken) {
                    DB::rollBack();
                    return [
                        'status'  => 'error',
                        'message' => 'Baki cuti tidak mencukupi'
                    ];
                }

                $sLeave->leave_taken   = $sLeave->leave_taken + $hTaken;
                $sLeave->leave_balance = $sLeave->leave_balance - $hTaken;
            }

            $sLeave->save();

            dispatch(new StaffLeaveJob($m->id, 'new-request'));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status'  => 'success',
            'message' => 'Permohonan Cuti Anda Sedang Menunggu Pengesahan'
        ];
    }

    /**
     * ✅ FIX + TAMBAH JOIN: paparkan nama jenis cuti kelompok
     */
    public function getRequestListByUserId(Request $request)
    {
        $user_id  = $request->user_id;
        $approval = $request->approval;

        $search = $request->get('search');
        $params = [];

        // Base WHERE
        $where = ' WHERE sle.old = 0 ';

        // Admin bypass / filter ikut requester vs approver
        if (!Auth::user()->hasRole('admin')) {
            if ($approval) {
                $where .= ' AND sa.user_id = ? ';
                $params[] = $user_id;
            } else {
                $where .= ' AND s.user_id = ? ';
                $params[] = $user_id;
            }
        }

        // Search
       if ($search) {
    $where .= ' AND (ustaff.name LIKE ? OR u.name LIKE ? OR ustaff.ic_no LIKE ?) ';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}


        $sql = '
            SELECT
                sle.id,
                sle.id as entry_id,
                sle.start_date,
                sle.end_date,
                sle.start_time,
                sle.end_time,
                sle.days,
                sle.leave_request_status_id as status_id,
                sle.reason,
                sle.created_at,
                sle.mc_upload,
                sle.leave_group_type_id,
                lgt.name as group_leave_type,
                lrs.name as l_status,
                u.name as approver_name,
                ustaff.name as request_by,
                lc.name as leave_category,
                lc.is_half_day,
                lc.is_mc,
                lc.is_full_day,
                lc.is_group_leave,
                sp.branch_id,
                sp.staff_id as requester_id
            FROM staff_leave_entries sle
            JOIN staff_positions sp ON sp.id = sle.staff_position_id
            JOIN staffs s ON s.id = sp.staff_id
            JOIN staffs sa ON sa.id = sle.approver_id
            JOIN users u ON u.id = sa.user_id
            JOIN users ustaff ON ustaff.id = s.user_id
            JOIN leave_request_statuses lrs ON lrs.id = sle.leave_request_status_id
            JOIN leave_categories lc ON lc.id = sle.leave_category_id
            LEFT JOIN leave_group_types lgt ON lgt.id = sle.leave_group_type_id
            ' . $where . '
            ORDER BY sle.created_at DESC
        ';

        return DB::select($sql, $params);
    }

    public function deleteRequest(Request $request)
    {
        $id = $request->id;

        DB::beginTransaction();
        try {
            $entry = $this->getStaffLeaveEntry($id);
            if (!$entry) {
                throw new \Exception('Rekod permohonan tidak dijumpai.');
            }

            $sLeave = $entry->getStaffLeave;
            $cat    = $entry->getLeaveCategory;

            if ($cat && $cat->is_mc) {
                $sLeave->mc_balance += $entry->days;
                $sLeave->mc_taken   -= $entry->days;

            } elseif ($cat && $cat->is_group_leave) {
                $sLeave->group_balance += $entry->days;
                $sLeave->group_taken   -= $entry->days;

            } else {
                $sLeave->leave_balance += $entry->days;
                $sLeave->leave_taken   -= $entry->days;
            }

            $sLeave->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function approveRequest(Request $request)
    {
        $approve_stat = $request->approve_stat;
        $id           = $request->id;

        DB::beginTransaction();
        try {
            $m = StaffLeaveEntry::with('getLeaveCategory', 'getStaffLeave')->find($id);
            if (!$m) {
                throw new \Exception('Rekod permohonan tidak dijumpai.');
            }

            $m->leave_request_status_id = $approve_stat == 1
                ? LeaveRequestStatus::APPROVED
                : LeaveRequestStatus::REJECTED;
            $m->save();

            if ($approve_stat == 2) {
                $sLeave = $m->getStaffLeave;
                $cat    = $m->getLeaveCategory;

                if ($cat && $cat->is_mc) {
                    $sLeave->mc_balance += $m->days;
                    $sLeave->mc_taken   -= $m->days;

                } elseif ($cat && $cat->is_group_leave) {
                    $sLeave->group_balance += $m->days;
                    $sLeave->group_taken   -= $m->days;

                } else {
                    $sLeave->leave_balance += $m->days;
                    $sLeave->leave_taken   -= $m->days;
                }

                $sLeave->save();
            }

            dispatch(new StaffLeaveJob($id, $approve_stat == 1 ? 'approve' : 'reject'));
            DB::commit();

            return [
                'status'  => 'success',
                'message' => $approve_stat == 1 ? 'Permohonan Diluluskan' : 'Permohonan Tidak Diluluskan'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function getApproverDropdown(Request $request)
    {
        $staff_id  = $request->staff_id;
        $branch_id = $request->branch_id;
        $search    = $request->search;

        $staff = Staff::select('id', 'user_id')->find($staff_id);
        $user  = $staff->getUser;

        $findApproverByRole = null;

        if ($user->hasRole('staff')) {
            $findApproverByRole = '(5)';
        } elseif ($user->hasRole('ketua_unit')) {
            $findApproverByRole = '(6, 7)';
        } elseif ($user->hasRole('penolong_pengarah')) {
            $findApproverByRole = '(7)';
        }

        if ($user) {
            $m = DB::select('
                SELECT
                s.id,
                u.name,
                sp.branch_position_id,
                ru.role_id
                FROM staffs s
                JOIN users u ON u.id = s.user_id
                JOIN staff_positions sp ON sp.staff_id = s.id
                JOIN branches b ON b.id = sp.branch_id
                JOIN role_user ru ON ru.user_id = u.id
                WHERE b.id = ?
                AND sp.deleted = false
                ' . ($findApproverByRole != null ? 'AND ru.role_id IN ' . $findApproverByRole : '') . '
                ' . ($search ? 'AND u.name LIKE "%' . $search . '%"' : '') . '
                LIMIT 10
            ', [$branch_id]);

            $data = [];
            if (count($m) > 0) {
                foreach ($m as $staff) {
                    $data[] = [
                        'id'   => $staff->id,
                        'text' => strtoupper($staff->name),
                    ];
                }
            }

            return $data;
        }

        return [];
    }

    public function approverUpdate(Request $request)
    {
        $approver_pick = $request->approver_pick;
        $id            = $request->id;

        $entry = StaffLeaveEntry::find($id);

        if ($entry->approver_id == $approver_pick) {
            return [
                'status'  => 'error',
                'message' => 'Pelulus Ini Sudah Dipilih Untuk Permohonan Ini'
            ];
        }

        $entry->approver_id = $approver_pick;
        $entry->save();
        dispatch(new StaffLeaveJob($entry->id, 'new-request'));

        return [
            'status'  => 'success',
            'message' => 'Pelulus Diubah'
        ];
    }

    public function leaveRequestChangeCategory(Request $request)
    {
        $change_to = $request->change_to;
        $id        = $request->id;

        DB::beginTransaction();
        try {
            $staffEntry       = StaffLeaveEntry::find($id);
            $getStaffPosition = $staffEntry->getStaffPosition;
            $getMc            = LeaveCategory::where('is_mc', true)->first();
            $getAnnual        = LeaveCategory::where('is_full_day', true)->first();
            $getStaffLeave    = StaffLeave::where('staff_position_id', $getStaffPosition->id)->first();
            $currentDay       = $staffEntry->days;

            if ($change_to == 'mc') {
                $staffEntry->leave_category_id = $getMc->id;
                // ✅ bila tukar kategori, reset jenis kelompok
                $staffEntry->leave_group_type_id = null;
                $staffEntry->save();

                $getStaffLeave->leave_taken   = $getStaffLeave->leave_taken - $currentDay;
                $getStaffLeave->leave_balance = $getStaffLeave->leave_balance + $currentDay;
                $getStaffLeave->mc_taken      = $getStaffLeave->mc_taken + $currentDay;
                $getStaffLeave->mc_balance    = $getStaffLeave->mc_balance - $currentDay;
                $getStaffLeave->save();
            }

            if ($change_to == 'annual') {
                $staffEntry->leave_category_id = $getAnnual->id;
                // ✅ bila tukar kategori, reset jenis kelompok
                $staffEntry->leave_group_type_id = null;
                $staffEntry->save();

                $getStaffLeave->leave_taken   = $getStaffLeave->leave_taken + $currentDay;
                $getStaffLeave->leave_balance = $getStaffLeave->leave_balance - $currentDay;
                $getStaffLeave->mc_taken      = $getStaffLeave->mc_taken - $currentDay;
                $getStaffLeave->mc_balance    = $getStaffLeave->mc_balance + $currentDay;
                $getStaffLeave->save();
            }

            DB::commit();
            return [
                'status'  => 'success',
                'message' => 'Permohonan Diubah Kepada ' . ($change_to == 'annual' ? 'Cuti Rehat' : 'Cuti Sakit'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ ADMIN ADJUST + AUTO APPROVE
     * Tambah sokongan: leave_group_type_id bila kategori baharu adalah group leave
     */
    public function adminAdjustAndAutoApprove(Request $request)
    {
        DB::beginTransaction();

        try {
            $entryId       = $request->entry_id;
            $newCategoryId = $request->leave_category_id;
            $dateRange     = $request->leave_date_range;
            $reason        = $request->reason;

            // ✅ TAMBAH: jenis cuti kelompok untuk adjust
            $leave_group_type_id = $request->leave_group_type_id;

            if (!$entryId) {
                throw new \Exception('Entry ID tidak diterima.');
            }

            if (!$newCategoryId) {
                throw new \Exception('Kategori baharu tidak diterima.');
            }

            if (!$dateRange) {
                throw new \Exception('Julat tarikh baharu tidak diterima.');
            }

            $oldEntry = StaffLeaveEntry::with('getLeaveCategory','getStaffLeave','getStaffPosition')
                ->where('id', $entryId)
                ->where(function($q){
                    $q->where('old', false)->orWhere('old', 0)->orWhereNull('old');
                })
                ->first();

            if (!$oldEntry) {
                throw new \Exception('Rekod asal tidak dijumpai atau telah dibatalkan.');
            }

            // ✅ benarkan admin adjust untuk PENDING atau APPROVED sahaja
            $allowed = [
                LeaveRequestStatus::PENDING,
                LeaveRequestStatus::APPROVED,
            ];
            if (!in_array((int)$oldEntry->leave_request_status_id, $allowed, true)) {
                throw new \Exception('Hanya permohonan PENDING / APPROVED boleh dibuat pertukaran oleh admin.');
            }

            $staffPosition = $oldEntry->getStaffPosition;
            if (!$staffPosition) {
                throw new \Exception('Maklumat penempatan staf tidak dijumpai.');
            }

            $staffLeave = $oldEntry->getStaffLeave;
            if (!$staffLeave) {
                $staffLeave = $this->staffLeaveRepository->checkExistRecord($staffPosition->id);
            }

            $oldCat = $oldEntry->getLeaveCategory;

            /** 1) Rollback baki cuti asal */
            if ($oldCat && $oldCat->is_mc) {
                $staffLeave->mc_taken   -= $oldEntry->days;
                $staffLeave->mc_balance += $oldEntry->days;

            } elseif ($oldCat && ($oldCat->is_group_leave ?? 0)) {
                $staffLeave->group_taken   -= $oldEntry->days;
                $staffLeave->group_balance += $oldEntry->days;

            } else {
                $staffLeave->leave_taken   -= $oldEntry->days;
                $staffLeave->leave_balance += $oldEntry->days;
            }

            // elak negatif
            $staffLeave->mc_taken    = max(0, (float)$staffLeave->mc_taken);
            $staffLeave->group_taken = max(0, (float)$staffLeave->group_taken);
            $staffLeave->leave_taken = max(0, (float)$staffLeave->leave_taken);

            $staffLeave->save();

            /** 2) Tanda rekod lama sebagai old/batal */
            $oldEntry->old = true;
            $oldEntry->reason = trim(
                ($oldEntry->reason ? $oldEntry->reason."\n" : '') .
                '[BATAL/PERTUKARAN PENTADBIR] ' . ($reason ?? '')
            );
            $oldEntry->save();

            /** 3) Validasi kategori baru + parse julat tarikh */
            $newCat = LeaveCategory::find($newCategoryId);
            if (!$newCat) {
                throw new \Exception('Kategori cuti baharu tidak sah.');
            }

            // ✅ jika kategori baru adalah group leave, wajib pilih jenis
            if (($newCat->is_group_leave ?? 0) == 1 && empty($leave_group_type_id)) {
                throw new \Exception('Sila pilih Jenis Cuti Kelompok.');
            }

            $range = explode(' to ', $dateRange);
            $start = $range[0] ?? null;
            $end   = $range[1] ?? ($range[0] ?? null);

            if (!$start || !$end) {
                throw new \Exception('Format julat tarikh tidak sah.');
            }

            /** 4) Kira hari bekerja */
            $days = $this->calculateWorkingDaysByPosition($staffPosition, $start, $end);

            if ($days <= 0) {
                throw new \Exception('Tarikh baharu adalah cuti umum/cuti mingguan. Tiada hari bekerja untuk diambil.');
            }

            // ✅ half-day: guna 0.5
            if ((int)$newCat->is_half_day === 1) {
                $days = 0.5 * $days;
            }

            /** 5) Create rekod baru (TERUS APPROVED) */
            $newEntry = new StaffLeaveEntry();
            $newEntry->staff_position_id       = $oldEntry->staff_position_id;
            $newEntry->staff_leave_id          = $staffLeave->id;
            $newEntry->approver_id             = $oldEntry->approver_id; // audit
            $newEntry->leave_category_id       = $newCategoryId;

            // ✅ set jenis kelompok jika perlu
            $newEntry->leave_group_type_id     = (($newCat->is_group_leave ?? 0) == 1)
                ? $leave_group_type_id
                : null;

            $newEntry->start_date              = $start;
            $newEntry->end_date                = $end;
            $newEntry->start_time              = null;
            $newEntry->end_time                = null;
            $newEntry->days                    = $days;

            $newEntry->reason                  = '[TINDAKAN PENTADBIR] ' . ($reason ?: 'Pertukaran cuti oleh pentadbir');

            $newEntry->leave_request_status_id = LeaveRequestStatus::APPROVED;
            $newEntry->old                     = false;
            $newEntry->save();

            /** 6) Tolak baki ikut kategori baru */
            if ($newCat->is_mc) {
                if ($staffLeave->mc_balance < $days) {
                    throw new \Exception('Baki Cuti Sakit tidak mencukupi.');
                }
                $staffLeave->mc_taken   += $days;
                $staffLeave->mc_balance -= $days;

            } elseif (($newCat->is_group_leave ?? 0)) {
                if ($staffLeave->group_balance < $days) {
                    throw new \Exception('Baki cuti kelompok tidak mencukupi.');
                }
                $staffLeave->group_taken   += $days;
                $staffLeave->group_balance -= $days;

            } else {
                if ($staffLeave->leave_balance < $days) {
                    throw new \Exception('Baki cuti tidak mencukupi.');
                }
                $staffLeave->leave_taken   += $days;
                $staffLeave->leave_balance -= $days;
            }

            $staffLeave->save();

            DB::commit();

            return [
                'status'  => 'success',
                'message' => 'Pertukaran cuti berjaya & terus diluluskan.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ Helper: kira hari bekerja berdasarkan state (exclude weekend + public holiday)
     */
    private function calculateWorkingDaysByPosition($staffPosition, string $startDate, string $endDate): float
    {
        $getBranchState = $staffPosition->getBranch->getState;
        $getWeekend     = $getBranchState->getWeekendHoliday;

        $yearArr = [
            date('Y', strtotime($startDate)),
            date('Y', strtotime($endDate)),
        ];

        $getPublic = PublicHoliday::whereIn('year', $yearArr)
            ->where('state_id', $getBranchState->id)
            ->where('h_date', '>=', $startDate)
            ->where('h_date', '<=', $endDate)
            ->get();

        $weekendH = [];
        foreach ($getWeekend as $wee) {
            $weekendH[] = $wee->getDay->name;
        }

        $publicH = [];
        foreach ($getPublic as $p) {
            $publicH[] = $p->h_date;
        }

        $hTaken = 0;
        $currentDate = $startDate;

        while ($currentDate <= $endDate) {
            $needAdd = true;

            if (in_array(date('l', strtotime($currentDate)), $weekendH)) {
                $needAdd = false;
            }

            if (in_array($currentDate, $publicH)) {
                $needAdd = false;
            }

            if ($needAdd) {
                $hTaken++;
            }

            $currentDate = date('Y-m-d', strtotime($currentDate.' +1 day'));
        }

        return $hTaken;
    }
}
