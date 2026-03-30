<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_competency_items', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->nullable()->index(); // contoh: K01
            $table->string('name');                          // contoh: Kerja Berpasukan
            $table->text('description')->nullable();

            $table->unsignedSmallInteger('weight')->default(1); // optional
            $table->unsignedSmallInteger('sort_order')->default(0)->index();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_competency_items');
    }
};
