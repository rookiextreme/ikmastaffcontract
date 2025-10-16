<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Repositories\DashboardRepository;
use App\Traits\CommonTrait;
use App\Traits\LookupTrait;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardRepository $dashboardRepository;
    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    use CommonTrait, LookupTrait;

    public function adminDashboard(Request $request){
        $currentYear = $request->get('year') ?? date('Y');

        $state = $this->getStates();
        $academic = $this->getAcademicQualifications();

        $statesById = $this->dashboardRepository->statesOnlyId($state);
        $statesByName = $this->dashboardRepository->statesOnlyName($state);
        $staffStateCount = $this->dashboardRepository->getStaffCountByState($statesById);
        $staffLeaveByCategoryCount = $this->dashboardRepository->getStaffLeaveCategoryCount($currentYear);
        $staffByAcademic = $this->dashboardRepository->getStaffByAcademic($currentYear);
        $academicByName = $this->dashboardRepository->academicsOnlyName($academic);

        return view('dashboard.admin-dashboard', [
            'state' => $state,
            'statesById' => $statesById,
            'statesByName' => $statesByName,
            'staffStateCount' => $staffStateCount,
            'staffLeaveByCategoryCount' => $staffLeaveByCategoryCount,
            'staffByAcademic' => $staffByAcademic,
            'academicByName' => $academicByName,
            'currentYear' => $currentYear,
        ]);
    }
}
