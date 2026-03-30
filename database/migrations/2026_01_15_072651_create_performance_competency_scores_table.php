<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_competency_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_id')
                ->constrained('performance_evaluations')
                ->cascadeOnDelete();

            $table->foreignId('competency_item_id')
                ->constrained('performance_competency_items')
                ->cascadeOnDelete();

            // markah PPP (utama)
            $table->decimal('score', 6, 2)->nullable(); // contoh 1-10 atau 1-5 (kita enforce di validation nanti)
            $table->text('comment')->nullable();

            $table->timestamps();

            $table->unique(['evaluation_id', 'competency_item_id'], 'uniq_eval_comp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_competency_scores');
    }
};
