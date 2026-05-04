<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->string('disposal_status', 30)->nullable()->after('disposal_date');
            $table->timestamp('disposal_submitted_at')->nullable()->after('disposal_status');
            $table->timestamp('disposal_approved_at')->nullable()->after('disposal_submitted_at');
            $table->foreignId('disposal_approved_by')->nullable()->after('disposal_approved_at')->constrained('users')->nullOnDelete();
            $table->text('disposal_admin_remark')->nullable()->after('disposal_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->dropForeign(['disposal_approved_by']);
            $table->dropColumn([
                'disposal_status',
                'disposal_submitted_at',
                'disposal_approved_at',
                'disposal_approved_by',
                'disposal_admin_remark',
            ]);
        });
    }
};