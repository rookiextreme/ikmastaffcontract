<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_positions', function (Blueprint $table) {
            // Tambah atau ubah kolum jika belum wujud
            if (!Schema::hasColumn('branch_positions', 'position_id')) {
                $table->unsignedBigInteger('position_id')->nullable()->after('branch_id');
            }

            if (!Schema::hasColumn('branch_positions', 'grade_id')) {
                $table->unsignedBigInteger('grade_id')->nullable()->after('position_id');
            }

            if (!Schema::hasColumn('branch_positions', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('grade_id');
            } else {
                $table->unsignedBigInteger('unit_id')->nullable()->change();
            }

            // Tambah foreign key baru dengan nama unik
            $table->foreign('branch_id', 'fk_branch_positions_branch')
                ->references('id')->on('branches')
                ->onDelete('cascade');

            $table->foreign('position_id', 'fk_branch_positions_position')
                ->references('id')->on('positions')
                ->onDelete('cascade');

            $table->foreign('grade_id', 'fk_branch_positions_grade')
                ->references('id')->on('grades')
                ->onDelete('cascade');

            $table->foreign('unit_id', 'fk_branch_positions_unit')
                ->references('id')->on('units')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('branch_positions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['unit_id']);
        });
    }
};
