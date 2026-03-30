<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_competency_items', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_competency_items', 'weight')) {
                $table->unsignedTinyInteger('weight')->nullable()->after('section'); // 50/25/20/5
            }
            if (!Schema::hasColumn('performance_competency_items', 'sort_order')) {
                $table->unsignedTinyInteger('sort_order')->default(0)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_competency_items', function (Blueprint $table) {
            if (Schema::hasColumn('performance_competency_items', 'weight')) {
                $table->dropColumn('weight');
            }
            if (Schema::hasColumn('performance_competency_items', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
