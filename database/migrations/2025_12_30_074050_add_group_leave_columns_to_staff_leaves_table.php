<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('staff_leaves', function (Blueprint $table) {
        $table->float('group_total')->default(0)->after('leave_balance');
        $table->float('group_taken')->default(0)->after('group_total');
        $table->float('group_balance')->default(0)->after('group_taken');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            //
        });
    }
};
