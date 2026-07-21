<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_assignments', function (Blueprint $table) {
            $table->string('pyd_group', 2)
                ->nullable()
                ->after('pyd_user_id')
                ->comment('A = Pengurusan dan Profesional, BC = Perkhidmatan Sokongan');
        });
    }

    public function down(): void
    {
        Schema::table('performance_assignments', function (Blueprint $table) {
            $table->dropColumn('pyd_group');
        });
    }
};