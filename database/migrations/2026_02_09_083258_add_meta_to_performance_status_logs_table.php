<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('performance_status_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_status_logs', 'meta')) {
                $table->json('meta')->nullable()->after('to_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_status_logs', function (Blueprint $table) {
            if (Schema::hasColumn('performance_status_logs', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
