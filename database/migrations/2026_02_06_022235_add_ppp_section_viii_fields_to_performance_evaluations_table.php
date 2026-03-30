<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            // Bahagian VIII (PPP) - ringkas & kemas
            $table->unsignedTinyInteger('ppp_supervise_years')->nullable()->after('ppp_comment');
            $table->unsignedTinyInteger('ppp_supervise_months')->nullable()->after('ppp_supervise_years');
            $table->longText('ppp_overall_performance')->nullable()->after('ppp_supervise_months');
            $table->longText('ppp_career_progress')->nullable()->after('ppp_overall_performance');
        });
    }

    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'ppp_supervise_years',
                'ppp_supervise_months',
                'ppp_overall_performance',
                'ppp_career_progress',
            ]);
        });
    }
};
