<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_periods', function (Blueprint $table) {
            // jenis tempoh: LNPT / SKT
            $table->string('type', 10)->default('LNPT')->after('year')->index();

            // sokong 2 kali setahun (1/2). optional dulu.
            $table->unsignedTinyInteger('session')->nullable()->after('type');

            // optional: index gabungan untuk elak duplicate year+type+session
            $table->unique(['year', 'type', 'session'], 'uniq_period_year_type_session');
        });
    }

    public function down(): void
    {
        Schema::table('performance_periods', function (Blueprint $table) {
            $table->dropUnique('uniq_period_year_type_session');
            $table->dropColumn(['type', 'session']);
        });
    }
};