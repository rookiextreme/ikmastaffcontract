<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->json('bahagian_ii_data')->nullable()->after('pyd_remarks');
        });
    }

    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn('bahagian_ii_data');
        });
    }
};
