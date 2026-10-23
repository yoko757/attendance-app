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
        Schema::create('attendance_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')->constrained()->cascadeOnDelete();
            $table->time('new_break_in')->nullable();
            $table->time('new_break_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_request_breaks');
    }
};
