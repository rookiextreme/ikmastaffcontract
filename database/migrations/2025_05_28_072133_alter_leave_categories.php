<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * ⚠️ Migration sepatutnya fokus schema.
         * Tapi kalau kamu memang nak "data fix" sekali,
         * guna DB::table supaya tak crash bila row tak wujud.
         */

        // Jika sebelum ini ada record id=3 dan nak tukar nama kepada "Cuti Sakit"
        DB::table('leave_categories')
            ->where('id', 3)
            ->update(['name' => 'Cuti Sakit']);
    }

    public function down(): void
    {
        // Optional: revert kalau perlu
        // DB::table('leave_categories')->where('id', 3)->update(['name' => 'MC']);
    }
};
