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
        Schema::table('staff_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_unit_id')->nullable()->after('branch_position_id');
            $table->foreign('branch_unit_id')->references('id')->on('branch_units');
        });

        Schema::table('staff_position_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_unit_id')->nullable()->after('branch_position_id');
            $table->foreign('branch_unit_id')->references('id')->on('branch_units');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
