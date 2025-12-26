<?php

namespace App\Repositories;

use App\Models\BranchPosition;
use App\Models\BranchUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchUnitRepository
{
    public function getAllUnitForBranch(Request $request){
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
            bu.id,
            bu.name
            FROM branch_units bu
            JOIN branches b ON bu.branch_id = b.id
            AND bu.deleted = false AND bu.branch_id = '.$branch_id.'
            '.$searchStr.'
        ', $params);

        return $m;
    }

    public function storeUpdate(Request $request){
        $unit_name = $request->unit_name;
        $branch_id = $request->branch_id;
        $id = $request->id;

        $check = $this->checkExist($unit_name, $branch_id, $id);
        DB::beginTransaction();
        try{
            if($check){
                return [
                    'status' => 'error',
                    'message' => 'Unit Ini Sudah Wujud'
                ];
            }

            $m = $id ? BranchUnit::find($id) : new BranchUnit;
            $m->branch_id = $branch_id;
            $m->name = $unit_name;
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

    public function checkExist($name, $branch_id, $id = false){
        $m = BranchUnit::where('branch_id', $branch_id)->where('name', $name)->where(function($query) use($id){
            if($id){
                $query->where('id', '!=', $id);
            }
        })->where('deleted', false)->first();

        return (bool)$m;
    }

    public function getBranchUnit($id){
        $data = [];
        $m = BranchUnit::find($id);
        $data['id'] = $m->id;
        $data['name'] = $m->name;

        return $data;
    }

    public function getUnitByBranch(Request $request){
        $branch_select = $request->branch_select;
        $search = $request->search;

        $m = DB::select('
            SELECT
            bu.id,
            bu.name
            FROM branch_units bu
            WHERE bu.branch_id = ?
            AND bu.deleted = false
            '.($search ? 'AND bu.name LIKE "%'.$search.'%"' : '').'
            LIMIT 10
        ', [
            $branch_select,
        ]);

        $data = [];
        if(count($m) > 0){
            foreach($m as $branch){
                $data[] = [
                    'id' => $branch->id,
                    'text' => strtoupper($branch->name),
                ];
            }
        }

        return $data;
    }
}
