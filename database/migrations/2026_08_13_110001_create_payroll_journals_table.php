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
        Schema::create('payroll_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->onDelete('cascade');
            $table->string('reference_number')->unique();
            $table->date('posting_date');
            $table->string('status')->default('posted'); // posted, reversed
            $table->unsignedBigInteger('total_debits_minor')->default(0);
            $table->unsignedBigInteger('total_credits_minor')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->timestamps();

            $table->index(['payroll_period_id', 'status']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_journal_id')->constrained('payroll_journals')->onDelete('cascade');
            $table->string('account_type'); // EXPENSE, LIABILITY, ASSET, HOLDING
            $table->string('account_name'); // e.g. "Salary Expense", "Payroll Payable", "Tax Holding"
            $table->string('entry_type'); // DEBIT, CREDIT
            $table->unsignedBigInteger('amount_minor')->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['payroll_journal_id', 'entry_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('payroll_journals');
    }
};
