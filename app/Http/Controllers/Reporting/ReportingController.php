<?php

namespace App\Http\Controllers\Reporting;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\Controller;
use App\Repositories\StaffPositionRepository;
use App\Traits\LookupTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\StaffHarta;

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

        $staff_name = $request->staff_name ?? null;
        $ic_no      = $request->ic_no ?? null;   // 👈 tambah
        $branch = $request->branch ?? null;
        $grade = $request->grade ?? null;
        $year_start = $request->year_start ?? null;
        $year_end = $request->year_end ?? null;

        $staffList = [];
        if($request->post()){
            $staffList = $this->staffPositionRepository->getStaffByRequest($request);
            
        }


        $pdfButton   = $request->find_pdf_generate;
        $excelButton = $request->find_excel_generate;

        // ==========================
        //  EXPORT EXCEL (PhpSpreadsheet)
        // ==========================
        if ($excelButton) {
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            // Tajuk kolum
            $sheet->setCellValue('A1', 'Bil');
            $sheet->setCellValue('B1', 'Nama Pegawai');
            $sheet->setCellValue('C1', 'No. KP');
            $sheet->setCellValue('D1', 'Cawangan');
            $sheet->setCellValue('E1', 'Gred');
            $sheet->setCellValue('F1', 'Jawatan');
            $sheet->setCellValue('G1', 'Unit');          // ✅ tambah
            $sheet->setCellValue('H1', 'Tarikh Mula');   // ✅ shift
            $sheet->setCellValue('I1', 'Tarikh Tamat');  // ✅ shift

                    $row = 2;
            $bil = 1;

            foreach ($staffList as $item) {
                $sheet->setCellValue('A' . $row, $bil);
                // ikut view: {{ ucwords($sl->name) }}
                $sheet->setCellValue('B' . $row, ucwords($item->name ?? ''));
                // No. KP – tak dipaparkan di view; biar kosong / tambah sendiri jika ada field
                $sheet->setCellValueExplicit(
    'C'.$row,
    (string) ($item->ic_number ?? ''),
    DataType::TYPE_STRING
);
                // ikut view: {{ $sl->branch_name }}
                $sheet->setCellValue('D' . $row, $item->branch_name ?? '');
                // ikut view: {{ $sl->grade }}
                $sheet->setCellValue('E' . $row, $item->grade ?? '');
                // ikut view: {{ $sl->position }}
                $sheet->setCellValue('F' . $row, $item->position ?? '');
                $sheet->setCellValue('G' . $row, $item->unit_name ?? ''); // ✅ tambah
                // ikut view: {{ $sl->start_date ?? '-' }}
                $sheet->setCellValue(
                    'H' . $row,
                    $item->start_date ? date('d-m-Y', strtotime($item->start_date)) : ''
                );
                // ikut view: {{ $sl->end_date ?? '-' }}
                $sheet->setCellValue(
                    'I' . $row,
                    $item->end_date ? date('d-m-Y', strtotime($item->end_date)) : ''
                );

                $row++;
                $bil++;
            }


            // Auto saiz kolum
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = 'Sejarah_Jawatan_' . date('Ymd_His') . '.xlsx';

            $response = new StreamedResponse(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            });

            $response->headers->set(
                'Content-Type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
            $response->headers->set('Cache-Control', 'max-age=0');

            return $response;
        }

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
            'staff_name' => $staff_name,
            'ic_no'       => $ic_no,   // 👈 hantar ke view
        ]);
    }

        public function harta(Request $request)
    {
        $currentYear = date("Y");
        $startYear = $currentYear - 10;

        $yearList = [];

        for ($year = $currentYear; $year >= $startYear; $year--) {
            $yearList[] = $year;
        }

        $branchList = $this->getBranches();

        $staff_name = $request->staff_name ?? null;
        $ic_no = $request->ic_no ?? null;
        $branch = $request->branch ?? null;
        $year = $request->year ?? null;
        $type = $request->type ?? null;
        $declaration_status = $request->declaration_status ?? null;
        $status_harta = $request->status_harta ?? null;

        $hartaList = [];

        if ($request->post()) {
            $query = StaffHarta::query()
                ->from('staff_hartas as sh')
                ->leftJoin('staffs as s', 's.id', '=', 'sh.staff_id')
                ->leftJoin('users as u', 'u.id', '=', 's.user_id')
                ->leftJoin('staff_positions as sp', function ($join) {
                    $join->on('sp.staff_id', '=', 's.id')
                        ->where('sp.deleted', 0);
                })
                ->leftJoin('branches as b', 'b.id', '=', 'sp.branch_id')
                ->select(
                    'sh.*',
                    'u.name',
                    'u.ic_no',
                    'b.name as branch_name'
                );

            if ($staff_name) {
                $query->where('u.name', 'like', '%' . $staff_name . '%');
            }

            if ($ic_no) {
                $icNoClean = str_replace(['-', ' '], '', $ic_no);

                $query->whereRaw(
                    "REPLACE(REPLACE(u.ic_no,'-',''),' ','') LIKE ?",
                    ["%{$icNoClean}%"]
                );
            }

            if ($branch) {
                $query->where('sp.branch_id', $branch);
            }

            if ($year) {
                $query->where('sh.year', $year);
            }

            if ($type) {
                $query->where('sh.type', 'like', '%' . $type . '%');
            }

            if ($declaration_status) {
                $query->where('sh.declaration_status', $declaration_status);
            }

            if ($status_harta == 'AKTIF') {
                $query->whereNull('sh.disposal_status');
            }

            if ($status_harta == 'DILUPUSKAN') {
                $query->whereNotNull('sh.disposal_status');
            }

            $hartaList = $query
                ->orderBy('u.name')
                ->orderByDesc('sh.year')
                ->get();
        }

        return view('reporting.harta', [
            'yearList' => $yearList,
            'branchList' => $branchList,
            'hartaList' => $hartaList,
            'staff_name' => $staff_name,
            'ic_no' => $ic_no,
            'branch' => $branch,
            'year' => $year,
            'type' => $type,
            'declaration_status' => $declaration_status,
            'status_harta' => $status_harta,
        ]);
    }
}
