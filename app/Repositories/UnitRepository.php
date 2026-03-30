<?php

namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitRepository
{
    public function getList(Request $request)
    {
        $search = $request->get('search');

        if ($search) {
            $searchStr = 'WHERE u.name LIKE ? AND u.deleted = false';
            $params = [$search.'%'];
        } else {
            $searchStr = 'WHERE u.deleted = false';
            $params = [];
        }

        $m = DB::select('
            SELECT
                u.id,
                u.name
            FROM units u
            '.$searchStr.'
        ', $params);

        return $m;
    }

    public function storeUpdate(Request $request)
    {
        $unit_name = $request->unit_name;
        $id = $request->id;

        $check = $this->checkExist($unit_name, $id);
        DB::beginTransaction();
        try {
            if ($check) {
                return [
                    'status' => 'error',
                    'message' => 'Unit Ini Sudah Wujud'
                ];
            }

            $m = $id ? Unit::find($id) : new Unit();
            $m->name = $unit_name;
            $m->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Unit '.(!$id ? 'ditambah' : 'dikemaskini')
        ];
    }

    public function checkExist($name, $id = false)
    {
        $m = Unit::where('name', $name)
            ->where(function ($query) use ($id) {
                if ($id) {
                    $query->where('id', '!=', $id);
                }
            })
            ->where('deleted', false)
            ->first();

        return (bool)$m;
    }

    public function getUnit($id)
    {
        $data = [];
        $m = Unit::find($id);
        $data['id'] = $m->id;
        $data['name'] = $m->name;

        return $data;
    }
}
