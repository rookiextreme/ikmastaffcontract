<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->decimal('disposal_value', 12, 2)
                ->nullable()
                ->after('disposal_date');
        });
    }

    public function down(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->dropColumn('disposal_value');
        });
    }
};