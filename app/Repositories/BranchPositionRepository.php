<?php

namespace App\Repositories;

use App\Models\BranchPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchPositionRepository
{
    public function getAllPositionForBranch(Request $request){
        $branch_id = $request->branch_id;
        $search = $request->get('search');
        $searchStr = '';

        if($search){
            $searchStr = 'WHERE b.name LIKE ? OR s.name LIKE ?';
            $params = [
                '%'.$search.'%',
                '%'.$search.'%',
            ];
        }else{
            $params = [
            ];
        }

        $m = DB::select('
            SELECT
            bp.id,
            bp.position,
            bp.grade,
            bp.default_holiday
            FROM branch_positions bp
            JOIN branches b ON bp.branch_id = b.id
            AND bp.deleted = false AND bp.branch_id = '.$branch_id.'
            '.$searchStr.'
        ', $params);

        return $m;
    }

    public function storeUpdate(Request $request){
        $position_name = $request->position_name;
        $position_grade = $request->position_grade;
        $position_holiday = $request->position_holiday;
        $branch_id = $request->branch_id;
        $id = $request->id;

        $check = $this->checkExist($position_name, $position_grade, $branch_id, $id);
        DB::beginTransaction();
        try{
            if($check){
                return [
                    'status' => 'error',
                    'message' => 'Penempatan Ini Sudah Wujud'
                ];
            }

            $m = $id ? BranchPosition::find($id) : new BranchPosition;
            $m->branch_id = $branch_id;
            $m->position = $position_name;
            $m->default_holiday = $position_holiday;
            $m->grade = $position_grade;
            $m->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Jawatan '.(!$id ? 'ditambah' : 'dikemaskini'),
            'id' => $m->id,
        ];
    }

    public function checkExist($name, $grade, $branch_id, $id = false){
        $m = BranchPosition::where('branch_id', $branch_id)->whereRaw('LOWER(position) = ?', [strtolower($name)])->whereRaw('LOWER(grade) = ?', [strtolower($grade)])->where(function($query) use($id){
            if($id){
                $query->where('id', '!=', $id);
            }
        })->where('deleted', false)->first();

        return (bool)$m;
    }

    public function getBranchPosition($id){
        $data = [];
        $m = BranchPosition::find($id);
        $data['id'] = $m->id;
        $data['name'] = $m->position;
        $data['grade'] = $m->grade;
        $data['holiday'] = $m->default_holiday;

        return $data;
    }
}
