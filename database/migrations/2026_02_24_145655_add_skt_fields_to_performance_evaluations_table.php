<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->json('skt_bahagian_i')->nullable()->after('bahagian_ii_data');
            $table->json('skt_bahagian_ii')->nullable()->after('skt_bahagian_i');
            $table->json('skt_bahagian_iii')->nullable()->after('skt_bahagian_ii');

            $table->timestamp('skt_submitted_at')->nullable()->after('skt_bahagian_iii');
            $table->timestamp('skt_ppp_reviewed_at')->nullable()->after('skt_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'skt_bahagian_i',
                'skt_bahagian_ii',
                'skt_bahagian_iii',
                'skt_submitted_at',
                'skt_ppp_reviewed_at',
            ]);
        });
    }
};