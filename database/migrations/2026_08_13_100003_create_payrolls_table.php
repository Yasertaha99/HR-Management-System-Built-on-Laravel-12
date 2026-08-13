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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('calculated');
            $table->unsignedBigInteger('gross_pay_minor')->default(0);
            $table->unsignedBigInteger('total_deductions_minor')->default(0);
            $table->unsignedBigInteger('net_pay_minor')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->timestamps();

            $table->unique(['payroll_period_id', 'user_id']);
            $table->index(['user_id', 'payroll_period_id']);
            $table->index('status');
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->string('type'); // base_salary, overtime, bonus, allowance, late_deduction...
            $table->string('code')->nullable();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1.00);
            $table->unsignedBigInteger('unit_amount_minor')->default(0);
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payroll_id', 'type']);
        });

        Schema::create('payroll_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->json('snapshot_data'); // Complete frozen calculation inputs, compensation rules, schedule & attendance stats
            $table->timestamps();

            $table->unique('payroll_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_snapshots');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
    }
};
