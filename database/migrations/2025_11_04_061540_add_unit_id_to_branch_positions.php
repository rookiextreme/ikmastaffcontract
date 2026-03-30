<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Elak duplicate jika pernah tambah secara manual
        if (!Schema::hasColumn('branch_positions', 'unit_id')) {
            Schema::table('branch_positions', function (Blueprint $table) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('grade_id');
                $table->foreign('unit_id')
                      ->references('id')->on('units')
                      ->nullOnDelete(); // sama dengan onDelete('set null')
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('branch_positions', 'unit_id')) {
            Schema::table('branch_positions', function (Blueprint $table) {
                // nama constraint auto biasanya: branch_positions_unit_id_foreign
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }
    }
};
