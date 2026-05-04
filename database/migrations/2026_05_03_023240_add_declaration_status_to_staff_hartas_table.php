<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->string('declaration_status', 30)->default('DRAFT')->after('year');
            $table->timestamp('submitted_at')->nullable()->after('declaration_status');
            $table->timestamp('returned_at')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('returned_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->text('admin_remark')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);

            $table->dropColumn([
                'declaration_status',
                'submitted_at',
                'returned_at',
                'approved_at',
                'approved_by',
                'admin_remark',
            ]);
        });
    }
};