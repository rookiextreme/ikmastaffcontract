<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_status_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('evaluation_id');

            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_role', 20)->nullable(); // pyd / ppp / ppk / admin

            $table->string('action', 50);                // SAVE_DRAFT / SUBMIT_PYD / SUBMIT_PPP / APPROVE_PPK
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('evaluation_id')
                ->references('id')->on('performance_evaluations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_status_logs');
    }
};
