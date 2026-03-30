<?php

namespace App\Repositories;

use App\Models\BranchPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchPositionRepository
{
    /**
     * Senarai jawatan bagi sesuatu cawangan.
     * Menyokong carian pada Position/Grade/Unit.
     */
    public function getAllPositionForBranch(Request $request)
    {
        $branch_id = (int) $request->branch_id;
        $search    = trim((string) $request->get('search'));

        $params = [$branch_id];
        $searchSql = '';

        if ($search !== '') {
            $searchSql = ' AND (p.name LIKE ? OR g.name LIKE ? OR COALESCE(u.name, "") LIKE ?)';
            $params[]  = '%'.$search.'%';
            $params[]  = '%'.$search.'%';
            $params[]  = '%'.$search.'%';
        }

        $sql = '
            SELECT
                bp.id,
                p.name AS position_name,
                g.name AS grade_name,
                u.name AS unit_name,
                bp.default_holiday
            FROM branch_positions bp
            JOIN positions p ON p.id = bp.position_id
            JOIN grades    g ON g.id = bp.grade_id
            LEFT JOIN units u ON u.id = bp.unit_id
            WHERE bp.deleted = FALSE
              AND bp.branch_id = ?
              '.$searchSql.'
            ORDER BY p.name, g.name
        ';

        return DB::select($sql, $params);
    }

    /**
     * Tambah / kemas kini jawatan cawangan.
     * Kawal duplicate (branch + position + grade + unit) — dengan unit boleh NULL.
     */
    public function storeUpdate(Request $request)
    {
        $id              = $request->id;
        $branch_id       = (int) $request->branch_id;
        $position_id     = (int) $request->position_name;
        $grade_id        = (int) $request->position_grade;
        $defaultHoliday  = (float) $request->position_holiday;

        // Normalisasi unit: "", "null", null => NULL sebenar; selain itu jadikan int
        $rawUnit  = $request->has('position_unit') ? $request->position_unit : null;
        $unit_id  = ($rawUnit === '' || $rawUnit === 'null' || $rawUnit === null) ? null : (int) $rawUnit;

        // Cegah rekod berganda
        $exists = BranchPosition::where('branch_id', $branch_id)
            ->where('position_id', $position_id)
            ->where('grade_id', $grade_id)
            ->where(function ($q) use ($unit_id) {
                if (is_null($unit_id)) {
                    $q->whereNull('unit_id');
                } else {
                    $q->where('unit_id', $unit_id);
                }
            })
            ->where('deleted', false)
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            return [
                'status'  => 'error',
                'message' => 'Penempatan ini sudah wujud',
            ];
        }

        DB::beginTransaction();
        try {
            $m = $id ? BranchPosition::find($id) : new BranchPosition();
            $m->branch_id       = $branch_id;
            $m->position_id     = $position_id;
            $m->grade_id        = $grade_id;
            $m->unit_id         = $unit_id;          // boleh NULL
            $m->default_holiday = $defaultHoliday;
            $m->save();

            DB::commit();

            return [
                'status'  => 'success',
                'message' => 'Jawatan ' . ($id ? 'dikemaskini' : 'ditambah'),
                'id'      => $m->id,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Data untuk pra-isi modal (edit).
     */
    public function getBranchPosition($id)
    {
        $m = BranchPosition::findOrFail($id);

        return [
            'id'      => $m->id,
            'name'    => $m->position_id,
            'grade'   => $m->grade_id,
            'unit'    => $m->unit_id,          // mungkin NULL
            'holiday' => $m->default_holiday,
        ];
    }

    /**
     * Untuk Select2 pada halaman Tetapan Jawatan staf.
     */
    public function getPositionByBranch(Request $request)
    {
        $branch_select = (int) $request->branch_select;
        $search        = trim((string) $request->search);

        $params = [$branch_select];
        $searchSql = '';

        if ($search !== '') {
            $searchSql = ' AND (p.name LIKE ? OR g.name LIKE ?)';
            $params[]  = '%'.$search.'%';
            $params[]  = '%'.$search.'%';
        }

        $sql = '
            SELECT
                bp.id,
                p.name AS position,
                g.name AS grade,
                COALESCE(u.name, "") AS unit
            FROM branch_positions bp
            JOIN positions p ON p.id = bp.position_id
            JOIN grades    g ON g.id = bp.grade_id
            LEFT JOIN units u ON u.id = bp.unit_id
            WHERE bp.branch_id = ?
              AND bp.deleted = FALSE
              '.$searchSql.'
            ORDER BY p.name, g.name
            LIMIT 20
        ';

        $rows = DB::select($sql, $params);

        $data = [];
        foreach ($rows as $r) {
            $label = strtoupper($r->position).' (GRED '.strtoupper($r->grade).')';
            if (!empty($r->unit)) {
                $label .= ' - '.strtoupper($r->unit);
            }
            $data[] = ['id' => $r->id, 'text' => $label];
        }

        return $data;
    }
}
