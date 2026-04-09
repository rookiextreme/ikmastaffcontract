<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('staff_hartas', function (Blueprint $table) {
        $table->id();

        // 🔥 ikut cara family (manual FK)
        $table->unsignedBigInteger('staff_id');

        $table->string('type'); // jenis harta
        $table->text('description')->nullable(); // keterangan
        $table->decimal('value', 12, 2)->nullable(); // nilai RM
        $table->string('year')->nullable(); // tahun perolehan

        $table->timestamps();

        // 🔥 foreign key sama style family
        $table->foreign('staff_id')
              ->references('id')
              ->on('staffs')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_hartas');
    }
};
