<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_competency_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_competency_scores', 'ppp_score')) {
                $table->unsignedTinyInteger('ppp_score')->nullable()->after('competency_item_id'); // 0-10
            }
            if (!Schema::hasColumn('performance_competency_scores', 'ppp_comment')) {
                $table->text('ppp_comment')->nullable()->after('ppp_score');
            }

            if (!Schema::hasColumn('performance_competency_scores', 'ppk_score')) {
                $table->unsignedTinyInteger('ppk_score')->nullable()->after('ppp_comment'); // 0-10
            }
            if (!Schema::hasColumn('performance_competency_scores', 'ppk_comment')) {
                $table->text('ppk_comment')->nullable()->after('ppk_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_competency_scores', function (Blueprint $table) {
            if (Schema::hasColumn('performance_competency_scores', 'ppp_score')) $table->dropColumn('ppp_score');
            if (Schema::hasColumn('performance_competency_scores', 'ppp_comment')) $table->dropColumn('ppp_comment');
            if (Schema::hasColumn('performance_competency_scores', 'ppk_score')) $table->dropColumn('ppk_score');
            if (Schema::hasColumn('performance_competency_scores', 'ppk_comment')) $table->dropColumn('ppk_comment');
        });
    }
};
