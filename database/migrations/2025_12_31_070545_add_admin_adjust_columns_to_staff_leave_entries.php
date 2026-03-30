<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('staff_leave_entries', function (Blueprint $table) {

            // ✅ ensure old wujud (sebab query awak guna sle.old=0)
            if (!Schema::hasColumn('staff_leave_entries', 'old')) {
                $table->boolean('old')->default(false)->after('leave_request_status_id');
            }

            // ✅ audit cancel
            if (!Schema::hasColumn('staff_leave_entries', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('old');
            }
            if (!Schema::hasColumn('staff_leave_entries', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('staff_leave_entries', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable()->after('cancelled_at');
            }

            // ✅ link rekod baru → rekod lama
            if (!Schema::hasColumn('staff_leave_entries', 'adjusted_from_entry_id')) {
                $table->unsignedBigInteger('adjusted_from_entry_id')->nullable()->after('cancel_reason');
            }

            // ✅ auto approve oleh admin
            if (!Schema::hasColumn('staff_leave_entries', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('adjusted_from_entry_id');
            }
            if (!Schema::hasColumn('staff_leave_entries', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_leave_entries', function (Blueprint $table) {
            foreach ([
                'old','cancelled_by','cancelled_at','cancel_reason',
                'adjusted_from_entry_id','approved_by','approved_at'
            ] as $col) {
                if (Schema::hasColumn('staff_leave_entries', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
