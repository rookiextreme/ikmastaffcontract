<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_categories', 'is_group_leave')) {
                $table->boolean('is_group_leave')->default(false)->after('is_mc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_categories', function (Blueprint $table) {
            if (Schema::hasColumn('leave_categories', 'is_group_leave')) {
                $table->dropColumn('is_group_leave');
            }
        });
    }
};
