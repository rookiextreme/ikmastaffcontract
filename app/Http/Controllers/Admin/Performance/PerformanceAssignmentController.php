<?php

namespace App\Http\Controllers\Admin\Performance;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PerformancePeriod;
use App\Repositories\Performance\PerformanceAssignmentRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerformanceAssignmentController extends Controller
{
    private PerformanceAssignmentRepository $repo;

    public function __construct(
        PerformanceAssignmentRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Tentukan tab semasa.
     */
    private function resolveTab(Request $request): string
    {
        $tab = strtolower(
            (string) $request->get('tab', 'lnpt')
        );

        return in_array(
            $tab,
            ['skt', 'lnpt'],
            true
        )
            ? $tab
            : 'lnpt';
    }

    /**
     * Tukarkan tab kepada type period.
     */
    private function resolveType(string $tab): string
    {
        return $tab === 'skt'
            ? 'SKT'
            : 'LNPT';
    }

    /**
     * Paparan senarai lantikan.
     */
    public function index(Request $request)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        $periods = PerformancePeriod::where('type', $type)
            ->orderByDesc('is_active')
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();

        $period = $this->repo->getActiveOrSelectedPeriod(
            $request->integer('period_id') ?: null,
            $type
        );

        if (!$period) {
            $assignments = collect();

            $users = User::orderBy('name')
                ->get([
                    'id',
                    'name',
                ]);

            $noActivePeriod = true;

            return view(
                'admin.performance.assignments.index',
                compact(
                    'periods',
                    'period',
                    'assignments',
                    'users',
                    'tab',
                    'type',
                    'noActivePeriod'
                )
            );
        }

        $assignments = $this->repo->listByPeriod(
            $period->id,
            $type
        );

        $users = User::orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $noActivePeriod = false;

        return view(
            'admin.performance.assignments.index',
            compact(
                'periods',
                'period',
                'assignments',
                'users',
                'tab',
                'type',
                'noActivePeriod'
            )
        );
    }

    /**
     * Simpan lantikan baharu.
     */
    public function store(Request $request)
    {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        $rules = [
            'performance_period_id' => [
                'required',
                'integer',
                'exists:performance_periods,id',
            ],

            'pyd_user_id' => [
                'required',
                'integer',
                'exists:users,id',

                Rule::unique(
                    'performance_assignments',
                    'pyd_user_id'
                )->where(function ($query) use ($request) {
                    return $query->where(
                        'performance_period_id',
                        $request->performance_period_id
                    );
                }),
            ],

            'ppp_user_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:pyd_user_id',
            ],
        ];

        /*
         * LNPT:
         * - wajib pilih kumpulan
         * - wajib PPK
         *
         * SKT:
         * - kumpulan tidak digunakan
         * - PPK tidak digunakan
         */
        if ($type === 'LNPT') {
            $rules['pyd_group'] = [
                'required',
                'string',
                Rule::in([
                    'A',
                    'BC',
                ]),
            ];

            $rules['ppk_user_id'] = [
                'required',
                'integer',
                'exists:users,id',
                'different:pyd_user_id',
            ];
        } else {
            $rules['pyd_group'] = [
                'nullable',
            ];

            $rules['ppk_user_id'] = [
                'nullable',
                'integer',
                'exists:users,id',
                'different:pyd_user_id',
            ];
        }

        $messages = [
            'pyd_group.required' => 'Sila pilih kumpulan perkhidmatan PYD.',
            'pyd_group.in'       => 'Pilihan kumpulan perkhidmatan tidak sah.',

            'pyd_user_id.unique' => 'PYD ini telah mempunyai lantikan bagi tempoh yang dipilih.',

            'ppp_user_id.required' => 'Sila pilih PPP.',
            'ppp_user_id.different' => 'PPP tidak boleh sama dengan PYD.',

            'ppk_user_id.required' => 'Sila pilih PPK.',
            'ppk_user_id.different' => 'PPK tidak boleh sama dengan PYD.',
        ];

        $data = $request->validate(
            $rules,
            $messages
        );

        /*
         * Pastikan period sepadan dengan tab.
         */
        $period = PerformancePeriod::findOrFail(
            $data['performance_period_id']
        );

        if (
            strtoupper(trim((string) $period->type))
            !== $type
        ) {
            return back()
                ->withErrors([
                    'performance_period_id'
                        => 'Tempoh tidak sepadan dengan tab yang dipilih.',
                ])
                ->withInput();
        }

        /*
         * SKT tidak menggunakan kumpulan dan PPK.
         */
        if ($type === 'SKT') {
            $data['pyd_group'] = null;
            $data['ppk_user_id'] = null;
        }

        /*
         * PPP dan PPK tidak boleh orang yang sama.
         */
        if (
            !empty($data['ppp_user_id'])
            && !empty($data['ppk_user_id'])
            && (int) $data['ppp_user_id']
                === (int) $data['ppk_user_id']
        ) {
            return back()
                ->withErrors([
                    'ppk_user_id'
                        => 'PPK tidak boleh sama dengan PPP.',
                ])
                ->withInput();
        }

        $this->repo->store($data);

        return redirect()
            ->route(
                'admin.performance.assignments.index',
                [
                    'tab'       => $tab,
                    'period_id' => $data['performance_period_id'],
                ]
            )
            ->with(
                'success',
                $type === 'SKT'
                    ? 'Lantikan PPP (SKT) berjaya disimpan.'
                    : 'Lantikan PPP/PPK berjaya disimpan.'
            );
    }

    /**
     * Kemaskini lantikan.
     */
    public function update(
        Request $request,
        $id
    ) {
        $tab  = $this->resolveTab($request);
        $type = $this->resolveType($tab);

        /*
         * SKT:
         * - hanya kemaskini PPP
         * - PPK dipaksa null
         * - kumpulan tidak diubah
         */
        if ($type === 'SKT') {
            $data = $request->validate(
                [
                    'ppp_user_id' => [
                        'required',
                        'integer',
                        'exists:users,id',
                    ],
                ],
                [
                    'ppp_user_id.required'
                        => 'Sila pilih PPP.',
                ]
            );

            $data['ppk_user_id'] = null;

            $this->repo->update(
                (int) $id,
                $data
            );

            return back()->with(
                'success',
                'Lantikan PPP (SKT) berjaya dikemaskini.'
            );
        }

        /*
         * LNPT:
         * - kumpulan wajib
         * - PPP dan PPK dikekalkan mengikut flow asal
         */
        $data = $request->validate(
            [
                'pyd_group' => [
                    'required',
                    'string',
                    Rule::in([
                        'A',
                        'BC',
                    ]),
                ],

                'ppp_user_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],

                'ppk_user_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
            ],
            [
                'pyd_group.required'
                    => 'Sila pilih kumpulan perkhidmatan PYD.',

                'pyd_group.in'
                    => 'Pilihan kumpulan perkhidmatan tidak sah.',
            ]
        );

        if (
            !empty($data['ppp_user_id'])
            && !empty($data['ppk_user_id'])
            && (int) $data['ppp_user_id']
                === (int) $data['ppk_user_id']
        ) {
            return back()->withErrors([
                'ppk_user_id'
                    => 'PPK tidak boleh sama dengan PPP.',
            ]);
        }

        $this->repo->update(
            (int) $id,
            $data
        );

        return back()->with(
            'success',
            'Lantikan berjaya dikemaskini.'
        );
    }

    /**
     * Padam lantikan.
     */
    public function destroy(
        Request $request,
        $id
    ) {
        $this->repo->destroy(
            (int) $id
        );

        return back()->with(
            'success',
            'Lantikan berjaya dipadam.'
        );
    }
}