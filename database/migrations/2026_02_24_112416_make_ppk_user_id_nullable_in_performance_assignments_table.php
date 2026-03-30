<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_assignments', function (Blueprint $table) {
            // pastikan ppk_user_id boleh null untuk SKT
            $table->unsignedBigInteger('ppk_user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('performance_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('ppk_user_id')->nullable(false)->change();
        });
    }
};