<?php

namespace App\Repositories\Performance;

use App\Models\PerformanceAssignment;
use App\Models\PerformancePeriod;
use Illuminate\Support\Facades\DB;

class PerformanceAssignmentRepository
{
    /**
     * Pilih period ikut TYPE (SKT/LNPT)
     *
     * - Kalau $periodId diberi, pastikan period wujud dan type sepadan.
     * - Kalau tidak diberi, ambil period aktif untuk type tersebut.
     * - Kalau tiada period aktif, ambil period terkini.
     */
    public function getActiveOrSelectedPeriod(
        ?int $periodId = null,
        string $type = 'LNPT'
    ): ?PerformancePeriod {
        $type = strtoupper(trim($type));

        if ($periodId) {
            return PerformancePeriod::where('id', $periodId)
                ->whereRaw(
                    'UPPER(TRIM(type)) = ?',
                    [$type]
                )
                ->first();
        }

        $active = PerformancePeriod::whereRaw(
                'UPPER(TRIM(type)) = ?',
                [$type]
            )
            ->where('is_active', 1)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();

        if ($active) {
            return $active;
        }

        return PerformancePeriod::whereRaw(
                'UPPER(TRIM(type)) = ?',
                [$type]
            )
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();
    }

    /**
     * Senarai lantikan ikut period dan type.
     */
    public function listByPeriod(
        int $periodId,
        string $type = 'LNPT'
    ) {
        $type = strtoupper(trim($type));

        $period = PerformancePeriod::where('id', $periodId)
            ->whereRaw(
                'UPPER(TRIM(type)) = ?',
                [$type]
            )
            ->first();

        if (!$period) {
            return collect();
        }

        return PerformanceAssignment::with([
                'pydUser',
                'pppUser',
                'ppkUser',
            ])
            ->where(
                'performance_period_id',
                $periodId
            )
            ->orderBy('pyd_user_id')
            ->get();
    }

    /**
     * Simpan lantikan baharu.
     */
    public function store(array $data): PerformanceAssignment
    {
        return DB::transaction(function () use ($data) {
            return PerformanceAssignment::create([
                'performance_period_id' => (int) $data['performance_period_id'],
                'pyd_user_id'           => (int) $data['pyd_user_id'],

                'pyd_group'             => $data['pyd_group'] ?? null,

                'ppp_user_id'           => !empty($data['ppp_user_id'])
                    ? (int) $data['ppp_user_id']
                    : null,

                'ppk_user_id'           => !empty($data['ppk_user_id'])
                    ? (int) $data['ppk_user_id']
                    : null,

                'unit_id'               => $data['unit_id'] ?? null,
                'branch_id'             => $data['branch_id'] ?? null,
            ]);
        });
    }

    /**
     * Kemaskini lantikan.
     */
    public function update(
        int $id,
        array $data
    ): PerformanceAssignment {
        return DB::transaction(function () use ($id, $data) {
            $row = PerformanceAssignment::findOrFail($id);

            $updateData = [
                'ppp_user_id' => !empty($data['ppp_user_id'])
                    ? (int) $data['ppp_user_id']
                    : null,

                'ppk_user_id' => !empty($data['ppk_user_id'])
                    ? (int) $data['ppk_user_id']
                    : null,
            ];

            /*
             * pyd_group hanya dikemaskini apabila dihantar.
             * Ini memastikan flow SKT tidak mengubah nilai kumpulan.
             */
            if (array_key_exists('pyd_group', $data)) {
                $updateData['pyd_group'] = $data['pyd_group'];
            }

            $row->update($updateData);

            return $row;
        });
    }

    /**
     * Padam lantikan.
     */
    public function destroy(int $id): void
    {
        PerformanceAssignment::where('id', $id)->delete();
    }
}