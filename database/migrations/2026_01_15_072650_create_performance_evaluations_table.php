<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('performance_period_id')
                ->constrained('performance_periods')
                ->cascadeOnDelete();

            $table->foreignId('assignment_id')
                ->constrained('performance_assignments')
                ->cascadeOnDelete();

            $table->foreignId('pyd_user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // status flow
            $table->string('status', 30)->default('DRAFT')->index();

            // tarikh penting
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('ppp_reviewed_at')->nullable();
            $table->timestamp('ppk_approved_at')->nullable();

            // ===== PYD input (contoh ringkas, nanti boleh expand) =====
            $table->longText('pyd_summary')->nullable();      // ringkasan hasil/sasaran
            $table->longText('pyd_remarks')->nullable();

            // ===== PPP input =====
            $table->decimal('ppp_total_score', 6, 2)->nullable();
            $table->longText('ppp_comment')->nullable();

            // ===== PPK input =====
            $table->decimal('ppk_total_score', 6, 2)->nullable(); // jika PPK override/final
            $table->longText('ppk_comment')->nullable();

            $table->timestamps();

            $table->unique(['performance_period_id', 'pyd_user_id'], 'uniq_period_eval_pyd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
