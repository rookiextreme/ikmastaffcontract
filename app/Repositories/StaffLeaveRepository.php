<?php

namespace App\Repositories;

use App\Models\StaffLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffLeaveRepository
{
    private StaffPositionRepository $staffPositionRepository;

    public function __construct(StaffPositionRepository $staffPositionRepository)
    {
        $this->staffPositionRepository = $staffPositionRepository;
    }

    /**
     * Pastikan rekod staff_leaves wujud untuk staff_position_id.
     * Jika tiada, create dengan default polisi (20 hari).
     */
    public function checkExistRecord($staff_position_id)
    {
        $m = StaffLeave::where('staff_position_id', $staff_position_id)->first();

        if (!$m) {
            $m = new StaffLeave();
            $m->staff_position_id = $staff_position_id;

            // ✅ default (ubah ikut polisi)
            $m->leave_total   = 20;
            $m->leave_taken   = 0;
            $m->leave_balance = 20;

            $m->group_total   = 20;
            $m->group_taken   = 0;
            $m->group_balance = 20;

            $m->save();
        }else{
        // ✅ kalau rekod sedia ada, pastikan kelompok ada default juga (sekali jalan)
        if($m->group_total == 0 && $m->group_taken == 0 && $m->group_balance == 0){
            $m->group_total = 20;
            $m->group_taken = 0;
            $m->group_balance = 20;
            $m->save();
        }
    }

        return $m;
    }

    /**
     * Update jumlah cuti tahunan (leave_total) sahaja.
     * ❌ Tidak kacau cuti kelompok & tidak kacau cuti lain.
     */
    public function storeUpdateNewLeaveBalance(Request $request)
    {
        $staff_id = $request->staff_id;
        $new_leave_balance = (int) $request->new_leave_balance;

        $m = $this->staffPositionRepository->getStaffPosition($staff_id);

        DB::beginTransaction();
        try {
            // ✅ pastikan rekod leave wujud (kalau belum ada, auto create default)
            $leave = $m->getStaffLeave ?: $this->checkExistRecord($m->id);

            // ✅ CUTI TAHUNAN sahaja (maintain behaviour asal)
            $leave->leave_total   = $new_leave_balance;
            $leave->leave_taken   = 0;
            $leave->leave_balance = $new_leave_balance;

            // ❌ TAK SENTUH group_total/group_taken/group_balance
            $leave->save();

            // maintain behaviour asal: tandakan rekod entry lama sebagai old
            $getOldEntries = $m->getStaffLeaveEntries;
            if (count($getOldEntries) > 0) {
                foreach ($getOldEntries as $oldEntry) {
                    $oldEntry->old = true;
                    $oldEntry->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Jumlah Cuti Telah Dikemaskini',
        ];
    }
}
