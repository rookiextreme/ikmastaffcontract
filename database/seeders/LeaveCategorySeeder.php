<?php

namespace Database\Seeders;

use App\Models\LeaveCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LeaveCategorySeeder extends Seeder
{
    public function run(): void
    {
        $hasGroupCol = Schema::hasColumn('leave_categories', 'is_group_leave');

        // name, is_full_day, is_half_day, is_mc, is_group_leave
        $data = [
            ['Cuti Rehat', true,  false, false, false],
            ['Kebenaran Keluar Pejabat', false, true,  false, false],
            ['Cuti Sakit', true,  false, true,  false],   // ✅ guna BM, logic MC kekal melalui is_mc
            ['Cuti Kelompok', true, false, false, true],  // ✅ kategori cuti kelompok
        ];

        foreach ($data as $d) {
            $payload = [
                'is_full_day' => (bool) $d[1],
                'is_half_day' => (bool) $d[2],
                'is_mc'       => (bool) $d[3],
            ];

            if ($hasGroupCol) {
                $payload['is_group_leave'] = (bool) $d[4];
            }

            LeaveCategory::updateOrCreate(
                ['name' => $d[0]],
                $payload
            );
        }
    }
}
