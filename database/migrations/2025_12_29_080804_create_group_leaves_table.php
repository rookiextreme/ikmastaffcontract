<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_leaves', function (Blueprint $table) {
            $table->id();

            // staf yang dipohonkan
            $table->unsignedBigInteger('staff_id');

            // admin yang mohonkan
            $table->unsignedBigInteger('applied_by');

            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_days')->default(0);

            $table->text('remarks')->nullable();

            // simpan path upload borang manual
            $table->string('attachment_path')->nullable();

            // status ringkas (boleh upgrade later)
            $table->string('status')->default('approved'); // approved/pending/rejected

            $table->timestamps();

            // FK (ikut table awak)
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['staff_id', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_leaves');
    }
};
