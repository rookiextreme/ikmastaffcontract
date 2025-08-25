<?php

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Repositories\StaffPositionRepository;
use App\Traits\LookupTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportingController extends Controller
{
    use LookupTrait;

    public StaffPositionRepository $staffPositionRepository;

    public function __construct()
    {
        $this->staffPositionRepository = new StaffPositionRepository();
    }

    public function index(Request $request){
        $currentYear = date("Y");
        $startYear = $currentYear - 10;

        $yearList = [];

        for ($year = $currentYear; $year >= $startYear; $year--) {
            $yearList[] = $year;
        }

        $gradeList = $this->getGrades();
        $branchList = $this->getBranches();

        $branch = $request->branch ?? null;
        $grade = $request->grade ?? null;
        $year_start = $request->year_start ?? null;
        $year_end = $request->year_end ?? null;

        $staffList = [];
        if($request->post()){
            $staffList = $this->staffPositionRepository->getStaffByRequest($request);
        }

        $normalButton = $request->find_normal_generate;
        $pdfButton  = $request->find_pdf_generate;

        if($pdfButton){
            $staffPdf = Pdf::loadView('pdf.position_history', [
                'staffList' => $staffList,
            ]);

            $staffPdf->setPaper('a4', 'landscape');
            $staffPdf->render();
            return $staffPdf->stream('Staff List '.date('d F Y').'.pdf');
            exit(0);
        }

        return view('reporting.position_history', [
            'yearList' => $yearList,
            'currentYear' => $currentYear,
            'gradeList' => $gradeList,
            'branchList' => $branchList,
            'staffList' => $staffList,
            'year_start' => $year_start,
            'year_end' => $year_end,
            'branch' => $branch,
            'grade' => $grade,
        ]);
    }
}
