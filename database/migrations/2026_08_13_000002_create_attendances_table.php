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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('attendance_date');
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->string('status')->default('working');
            $table->unsignedInteger('total_minutes')->nullable();
            $table->unsignedInteger('rounded_hours')->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // MANDATORY CONSTRAINT: Each user can have only one attendance record per date
            $table->unique(['user_id', 'attendance_date']);

            // Performance indexes for fast querying and reporting
            $table->index(['user_id', 'attendance_date']);
            $table->index('attendance_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
