<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->unsignedTinyInteger('ppk_supervise_years')->nullable()->after('ppp_career_progress');
            $table->unsignedTinyInteger('ppk_supervise_months')->nullable()->after('ppk_supervise_years');
        });
    }

    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn(['ppk_supervise_years','ppk_supervise_months']);
        });
    }
};
