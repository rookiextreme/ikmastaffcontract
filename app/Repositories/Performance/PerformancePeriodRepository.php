<?php

namespace App\Repositories\Performance;

use App\Models\PerformancePeriod;
use Illuminate\Support\Facades\DB;

class PerformancePeriodRepository
{
    /**
     * List period ikut type:
     * - LNPT (default)
     * - SKT
     */
    public function list(string $type = 'LNPT')
    {
        $type = strtoupper(trim($type));

        return PerformancePeriod::query()
            ->where('type', $type)
            ->orderByDesc('is_active')
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->get();
    }

    /**
     * Store period (LNPT/SKT)
     * - jika is_active=1: auto-off period lain untuk type yang sama
     */
    public function store(array $data): PerformancePeriod
    {
        $type = strtoupper(trim($data['type'] ?? 'LNPT'));

        return DB::transaction(function () use ($data, $type) {

            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($isActive) {
                PerformancePeriod::where('type', $type)->update(['is_active' => 0]);
            }

            $payload = $data;
            $payload['type'] = $type;
            $payload['is_active'] = $isActive;

            return PerformancePeriod::create($payload);
        });
    }

    /**
     * Update period
     * - jika is_active=1: auto-off period lain type yang sama
     */
    public function update(int $id, array $data): PerformancePeriod
    {
        return DB::transaction(function () use ($id, $data) {

            $period = PerformancePeriod::findOrFail($id);

            $type = strtoupper(trim($period->type ?? ($data['type'] ?? 'LNPT')));

            $isActive = !empty($data['is_active']) ? 1 : 0;

            if ($isActive) {
                PerformancePeriod::where('type', $type)
                    ->where('id', '!=', $period->id)
                    ->update(['is_active' => 0]);
            }

            $payload = $data;
            // ✅ jangan benarkan tukar type melalui update biasa (selamat)
            unset($payload['type']);

            $payload['is_active'] = $isActive;

            $period->update($payload);

            return $period;
        });
    }

    /**
     * Activate period by id
     * - auto-off period lain utk type yang sama sahaja
     */
    public function activate(int $id): void
    {
        DB::transaction(function () use ($id) {

            $period = PerformancePeriod::findOrFail($id);
            $type = strtoupper(trim($period->type ?? 'LNPT'));

            PerformancePeriod::where('type', $type)->update(['is_active' => 0]);

            $period->update(['is_active' => 1]);
        });
    }

    /**
     * Optional helper: get active period ikut type
     */
    public function getActive(string $type = 'LNPT'): ?PerformancePeriod
    {
        $type = strtoupper(trim($type));

        return PerformancePeriod::query()
            ->where('type', $type)
            ->where('is_active', 1)
            ->orderByDesc('year')
            ->orderByDesc('session')
            ->first();
    }
}