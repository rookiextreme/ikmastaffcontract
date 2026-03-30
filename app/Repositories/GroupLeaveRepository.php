<?php

namespace App\Repositories;

use App\Models\GroupLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupLeaveRepository
{
    public function listLatest(int $limit = 200)
    {
        return GroupLeave::query()
            ->with(['staff.getUser', 'appliedBy'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function searchStaff(Request $request, int $limit = 20)
    {
        $term = trim((string) $request->get('q', ''));

        $allow = ['staff','ketua_unit','penolong_pengarah','ketua_pengarah'];
        $deny  = ['admin','super-admin','approval-admin'];

        $spLatest = DB::table('staff_positions')
            ->selectRaw('MAX(id) AS id, staff_id')
            ->groupBy('staff_id');

        $q = DB::table('staffs as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoinSub($spLatest, 'spm', fn($j)=>$j->on('spm.staff_id','=','s.id'))
            ->leftJoin('staff_positions as sp', 'sp.id', '=', 'spm.id')
            ->leftJoin('branches as b', 'b.id', '=', 'sp.branch_id')
            ->leftJoin('branch_positions as bp', 'bp.id', '=', 'sp.branch_position_id')
            ->leftJoin('positions as p', 'p.id', '=', 'bp.position_id')
            ->leftJoin('units as un', 'un.id', '=', 'bp.unit_id')

            // roles allowed
            ->whereExists(function ($s) use ($allow) {
                $s->from('role_user as ru')
                    ->join('roles as r','r.id','=','ru.role_id')
                    ->whereColumn('ru.user_id','u.id')
                    ->whereIn('r.name', $allow)
                    ->select(DB::raw(1));
            })
            // roles denied
            ->whereNotExists(function ($s) use ($deny) {
                $s->from('role_user as ru')
                    ->join('roles as r','r.id','=','ru.role_id')
                    ->whereColumn('ru.user_id','u.id')
                    ->whereIn('r.name', $deny)
                    ->select(DB::raw(1));
            });

        if ($term !== '') {
            $q->where(function($qq) use ($term) {
                $qq->where('u.name', 'like', "%{$term}%")
                    ->orWhere('u.ic_no', 'like', "%{$term}%")
                    ->orWhere('u.no_staff', 'like', "%{$term}%");
            });
        }

        return $q->select([
                's.id as staff_id',
                'u.name',
                'u.ic_no',
                'u.no_staff',
                DB::raw('COALESCE(p.name, "-") as position_name'),
                DB::raw('COALESCE(b.name, "-") as branch_name'),
                DB::raw('COALESCE(un.name, "-") as unit_name'),
            ])
            ->orderBy('u.name')
            ->limit($limit)
            ->get();
    }

    public function create(array $data): GroupLeave
    {
        // enforce admin applied_by
        $data['applied_by'] = Auth::id();

        // default status ikut flow awak (admin terus create)
        $data['status'] = $data['status'] ?? 'approved';

        return GroupLeave::create($data);
    }
}
