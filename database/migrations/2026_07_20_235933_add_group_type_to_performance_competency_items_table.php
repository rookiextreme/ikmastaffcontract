<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_competency_items', function (Blueprint $table) {
            $table->string('group_type', 10)
                ->default('ALL')
                ->after('code')
                ->index()
                ->comment('ALL = semua kumpulan, A = Kumpulan A, BC = Kumpulan B/C');
        });
    }

    public function down(): void
    {
        Schema::table('performance_competency_items', function (Blueprint $table) {
            $table->dropIndex(['group_type']);
            $table->dropColumn('group_type');
        });
    }
};