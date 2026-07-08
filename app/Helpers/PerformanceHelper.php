<?php

namespace App\Helpers;

class PerformanceHelper
{
    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'BELUM_JANA'      => 'Belum Dijana',
            'DRAFT'           => 'Draf',
            'SUBMITTED'       => 'Menunggu Semakan PPP',
            'RETURNED_BY_PPP' => 'Dipulangkan Untuk Pembetulan',
            'PPP_SCORED'      => 'Menunggu Pengesahan PPK',
            'PPP_REVIEWED'    => 'Disahkan Oleh PPP',
            'PPK_APPROVED'    => 'Disahkan Oleh PPK',
            'FINAL'           => 'Muktamad',
            default           => $status ?: '-',
        };
    }

    public static function statusBadge(?string $status): string
    {
        $label = self::statusLabel($status);

        $class = match ($status) {
            'BELUM_JANA'      => 'badge-light-dark',
            'DRAFT'           => 'badge-light-secondary',
            'SUBMITTED'       => 'badge-light-primary',
            'RETURNED_BY_PPP' => 'badge-light-warning',
            'PPP_SCORED'      => 'badge-light-warning',
            'PPP_REVIEWED'    => 'badge-light-success',
            'PPK_APPROVED'    => 'badge-light-success',
            'FINAL'           => 'badge-light-info',
            default           => 'badge-light-dark',
        };

        return '<span class="badge '.$class.'">'.$label.'</span>';
    }

    public static function moduleLabel(?string $module): string
    {
        return match ($module) {
            'SKT'  => 'SKT',
            'LNPT' => 'LNPT',
            default => '-',
        };
    }

    public static function moduleBadge(?string $module): string
    {
        $label = self::moduleLabel($module);

        $class = match ($module) {
            'SKT'  => 'badge-light-primary',
            'LNPT' => 'badge-light-success',
            default => 'badge-light-secondary',
        };

        return '<span class="badge '.$class.'">'.$label.'</span>';
    }

    public static function actionLabel(?string $action): string
{
    return match ($action) {

        // ===================
        // SKT
        // ===================
        'SUBMIT_PYD_SKT'  => 'PYD Menghantar SKT',
        'REVIEW_PPP_SKT'  => 'PPP Menyemak SKT',
        'RETURN_PPP_SKT'  => 'PPP Memulangkan SKT Kepada PYD',

        // ===================
        // LNPT
        // ===================
        'SUBMIT_PYD'      => 'PYD Menghantar Penilaian',
        'SUBMIT_PPP'      => 'PPP Menghantar Penilaian Kepada PPK',
        'REVIEW_PPP'      => 'PPP Menyemak Penilaian',
        'APPROVE_PPK'     => 'PPK Mengesahkan Penilaian',

        // ===================
        // ADMIN
        // ===================
        'ADMIN_FINALIZE'      => 'Admin Memuktamadkan Penilaian',
        'UPDATE_PPSM_SCORE'   => 'Admin Mengemaskini Markah PPSM',
        'UPDATE_PPSM'         => 'Admin Mengemaskini Markah PPSM',
        'ADMIN_UPDATE_PYD'    => 'Admin Mengemaskini Maklumat PYD',

        default => ucwords(strtolower(str_replace('_', ' ', $action ?? '-'))),
    };
}
}