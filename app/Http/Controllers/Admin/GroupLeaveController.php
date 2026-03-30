<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\GroupLeaveRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupLeaveController extends Controller
{
    public function __construct(public GroupLeaveRepository $repo) {}

    public function index()
    {
        $list = $this->repo->listLatest();
        return view('admin.group_leave.index', compact('list'));
    }

    public function create()
    {
        return view('admin.group_leave.create');
    }

    public function searchStaff(Request $request)
    {
        return response()->json($this->repo->searchStaff($request));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id'   => ['required', 'integer', 'exists:staffs,id'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'total_days' => ['required', 'integer', 'min:1'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'remarks'    => ['nullable', 'string', 'max:2000'],
        ]);

        DB::beginTransaction();
        $path = null;

        try {
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('uploads/group_leave', 'public');
            }

            $this->repo->create([
                'staff_id'        => (int) $request->staff_id,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'total_days'      => (int) $request->total_days,
                'remarks'         => $request->remarks,
                'attachment_path' => $path,
                // 'status' => 'approved' // optional
            ]);

            DB::commit();

            return redirect()
                ->route('admin.group_leave.index')
                ->with('success', 'Permohonan Cuti Kelompok berjaya disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($path) Storage::disk('public')->delete($path);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
