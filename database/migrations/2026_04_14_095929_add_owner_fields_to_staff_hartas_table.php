<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->string('owner_type')->default('self')->after('staff_id');
            $table->unsignedBigInteger('family_id')->nullable()->after('owner_type');
            $table->string('owner_name')->nullable()->after('family_id');

            $table->foreign('family_id')
                ->references('id')
                ->on('staff_families')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('staff_hartas', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropColumn(['owner_type', 'family_id', 'owner_name']);
        });
    }
};