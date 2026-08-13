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
        Schema::create('employee_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('compensation_type')->default('monthly'); // monthly, hourly, daily
            $table->unsignedBigInteger('base_salary_minor')->default(0); // 100050 = 1000.50 EGP
            $table->unsignedBigInteger('hourly_rate_minor')->default(0);
            $table->unsignedBigInteger('daily_rate_minor')->default(0);
            $table->decimal('overtime_multiplier', 4, 2)->default(1.50); // 1.5x overtime rate
            $table->string('currency', 3)->default('EGP');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'effective_from', 'effective_to']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_compensations');
    }
};
