<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PerformanceCompetencyItem;

class PerformanceCompetencyItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // =========================
            // III: Penghasilan Kerja (50%)
            // =========================
            [
                'code' => 'III', 'weight' => 50, 'sort_order' => 1,
                'name' => 'Kuantiti Hasil Kerja',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'III', 'weight' => 50, 'sort_order' => 2,
                'name' => 'Kualiti Hasil Kerja (Kesempurnaan, teratur & kemas)',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'III', 'weight' => 50, 'sort_order' => 3,
                'name' => 'Kualiti Hasil Kerja (Usaha & inisiatif untuk mencapai kesempurnaan)',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'III', 'weight' => 50, 'sort_order' => 4,
                'name' => 'Ketepatan Masa',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'III', 'weight' => 50, 'sort_order' => 5,
                'name' => 'Keberkesanan Hasil Kerja',
                'description' => null,
                'is_active' => 1,
            ],

            // =========================
            // IV: Pengetahuan & Kemahiran (25%)
            // =========================
            [
                'code' => 'IV', 'weight' => 25, 'sort_order' => 1,
                'name' => 'Ilmu pengetahuan & kemahiran dalam bidang kerja',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'IV', 'weight' => 25, 'sort_order' => 2,
                'name' => 'Pelaksanaan dasar, peraturan & arahan pentadbiran',
                'description' => null,
                'is_active' => 1,
            ],
            [
                'code' => 'IV', 'weight' => 25, 'sort_order' => 3,
                'name' => 'Keberkesanan komunikasi',
                'description' => null,
                'is_active' => 1,
            ],

            // =========================
// V: Kualiti Peribadi (20%)
// =========================
[
    'code' => 'V',
    'weight' => 20,
    'sort_order' => 1,
    'group_type' => 'A',
    'name' => 'Ciri-ciri Pemimpin',
    'description' => 'Mempunyai wawasan, komitmen, kebolehan membuat keputusan, menggerak dan memberi dorongan kepada pegawai ke arah pencapaian objektif organisasi.',
    'is_active' => 1,
],
[
    'code' => 'V',
    'weight' => 20,
    'sort_order' => 2,
    'group_type' => 'ALL',
    'name' => 'Kebolehan mengelola',
    'description' => null,
    'is_active' => 1,
],
[
    'code' => 'V',
    'weight' => 20,
    'sort_order' => 3,
    'group_type' => 'ALL',
    'name' => 'Disiplin',
    'description' => null,
    'is_active' => 1,
],
[
    'code' => 'V',
    'weight' => 20,
    'sort_order' => 4,
    'group_type' => 'ALL',
    'name' => 'Proaktif & inovatif',
    'description' => null,
    'is_active' => 1,
],
[
    'code' => 'V',
    'weight' => 20,
    'sort_order' => 5,
    'group_type' => 'ALL',
    'name' => 'Jalinan hubungan & kerjasama',
    'description' => null,
    'is_active' => 1,
],
            // =========================
            // VI: Kegiatan & Sumbangan Luar Tugas (5%)
            // =========================
           [
    'code' => 'VI', 'weight' => 5, 'sort_order' => 1,
    'name' => 'Kegiatan & sumbangan di luar tugas rasmi',

    'description' =>
        "KEGIATAN DAN SUMBANGAN DI LUAR TUGAS RASMI\n\n" .
        "Berasaskan maklumat di Bahagian II perenggan 1, Pegawai Penilai dikehendaki memberi\n" .
        "penilaian dengan menggunakan skel 1 hingga 10.\n" .
        "Tiada sebarang markah boleh diberikan (kosong) jika PYD tidak mencatat kegiatan atau\n" .
        "sumbangannya.\n\n" .
        "Peringkat: Komuniti / Jabatan / Daerah / Negeri / Negara / Antarabangsa.",

    'is_active' => 1,
],
        ];

        foreach ($items as $it) {
            PerformanceCompetencyItem::updateOrCreate(
                // kunci unik logik: code + name
                ['code' => $it['code'], 'name' => $it['name']],
                [
                    'description' => $it['description'],
                    'weight'      => $it['weight'],
                    'sort_order'  => $it['sort_order'],
                    'is_active'   => $it['is_active'],
                    'is_active'   => $it['is_active'],
                ]
            );
        }
    }
}
