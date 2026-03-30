<?php

namespace App\Http\Controllers\Admin\Setting;

use App\Http\Controllers\Controller;
use App\Library\Datatable\SymTable;
use App\Models\Unit;
use App\Repositories\UnitRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
use Illuminate\Http\Request;

class UnitSettingController extends Controller
{
    use LookupTrait, CommonTrait;
    private UnitRepository $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function index()
    {
        return view('admin.setting.unit.index');
    }

    public function list(Request $request)
    {
        $model = $this->unitRepository->getList($request);

        return SymTable::of($model)
            ->addRowAttr([
                'data-id' => fn($data) => $data->id
            ])
            ->addColumn('name', fn($data) => strtoupper($data->name))
            ->make();
    }

    public function storeUpdate(Request $request)
    {
        $m = $this->unitRepository->storeUpdate($request);
        return $this->setResponse($m['message'], !($m['status'] == 'error'));
    }

    public function getUnit(Request $request)
    {
        $m = $this->unitRepository->getUnit($request->id);
        return $this->setDataResponse($m);
    }

    public function deleteUnit(Request $request)
    {
        return $this->setResponse($this->setDelete(Unit::class, $request->id, 'Unit'));
    }
}
