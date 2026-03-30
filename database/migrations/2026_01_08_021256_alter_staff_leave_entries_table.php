<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('staff_leave_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_leave_entries', 'leave_group_type_id')) {
                $table->unsignedBigInteger('leave_group_type_id')
                    ->nullable()
                    ->after('leave_category_id');

                $table->foreign('leave_group_type_id')
                    ->references('id')
                    ->on('leave_group_types')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_leave_entries', function (Blueprint $table) {
            if (Schema::hasColumn('staff_leave_entries', 'leave_group_type_id')) {
                $table->dropForeign(['leave_group_type_id']);
                $table->dropColumn('leave_group_type_id');
            }
        });
    }
};
