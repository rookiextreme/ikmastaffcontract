<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->string('owner_relation')->nullable()->after('owner_name');
        });
    }

    public function down(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->dropColumn('owner_relation');
        });
    }
};