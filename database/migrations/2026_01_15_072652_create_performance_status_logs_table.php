<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluation_id')
                ->constrained('performance_evaluations')
                ->cascadeOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('changed_at')->useCurrent();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['evaluation_id', 'to_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_status_logs');
    }
};
