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
        Schema::table('performance_evaluations', function (Blueprint $table) {

            // Catatan PPP apabila memulangkan SKT
            $table->text('ppp_return_remark')
                  ->nullable()
                  ->after('skt_ppp_reviewed_at');

            // Tarikh dan masa SKT dipulangkan
            $table->timestamp('ppp_returned_at')
                  ->nullable()
                  ->after('ppp_return_remark');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_evaluations', function (Blueprint $table) {

            $table->dropColumn([
                'ppp_return_remark',
                'ppp_returned_at',
            ]);

        });
    }
};