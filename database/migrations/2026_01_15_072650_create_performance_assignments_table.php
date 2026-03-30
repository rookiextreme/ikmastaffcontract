<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_period_id')
                ->constrained('performance_periods')
                ->cascadeOnDelete();

            // PYD (yang dinilai)
            $table->foreignId('pyd_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // PPP (penilai 1)
            $table->foreignId('ppp_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // PPK (pengesah)
            $table->foreignId('ppk_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // optional: ikut unit/cawangan jika perlu report
            $table->unsignedBigInteger('unit_id')->nullable()->index();     // kalau ada table units
            $table->unsignedBigInteger('branch_id')->nullable()->index();   // kalau ada table branches

            $table->timestamps();

            $table->unique(['performance_period_id', 'pyd_user_id'], 'uniq_period_pyd');
            $table->index(['performance_period_id', 'ppp_user_id']);
            $table->index(['performance_period_id', 'ppk_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_assignments');
    }
};
