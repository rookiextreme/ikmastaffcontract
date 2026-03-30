<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_periods', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('year')->index(); // 2026
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_active')->default(false)->index();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique(['year']); // 1 period per year
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_periods');
    }
};
