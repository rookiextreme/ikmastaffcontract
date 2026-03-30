<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveGroupTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Aktiviti Khidmat Masyarakat',
            'Christmas Eve',
            'Deepavali',
            'Good Friday',
            'Hadir Mahkamah Luar Ibu Pejabat',
            'Hari Mengundi',
            'Hari Raya Haji Kedua',
            'Isteri Bersalin',
            'Jurulatih Program Latihan Khidmat Negara (PLKN)',
            'Keagamaan',
            'Lawatan Kebudayaan Di Luar Negeri',
            'Mengambil Peperiksaan',
            'Menghadiri Aktiviti Koperasi',
            'Menghadiri mesyuarat/bengkel/seminar anjuran Pihak Pekerja MBK',
            'Menjaga Ahli Keluarga Sakit',
            'Pertandingan Musabaqah Al-Quran',
            'Program Pertukaran Peringkat Antarabangsa',
            'Rawatan Hemodialisis',
            'Sumbangan Ilmu Peringkat Antarabangsa',
            'Thaipusam',
            'Umrah',
            'Urusan Kematian Ahli Keluarga Terdekat',
        ];

        foreach ($items as $i => $name) {
            DB::table('leave_group_types')->updateOrInsert(
                ['name' => $name],
                [
                    'sort' => $i + 1,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
