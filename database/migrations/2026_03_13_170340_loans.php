<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();

            $table->string('user_id', 10);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // SSS or PAG-IBIG
            $table->enum('loan_type', ['sss', 'pagibig']);
            $table->string('loan_type_name', 100); // e.g. "SSS Salary Loan", "PAG-IBIG MPL"

            $table->decimal('amount', 12, 2);                    // original loan amount
            $table->decimal('monthly_amortization', 10, 2);      // fixed monthly deduction
            $table->unsignedSmallInteger('term_months');          // total number of payments

            $table->date('start_date');
            $table->date('completed_date')->nullable();

            $table->enum('status', ['active', 'completed', 'cancelled'])
                  ->default('active');

            // Who encoded this into the system (HR)
            $table->string('encoded_by', 10)->nullable();
            $table->foreign('encoded_by')->references('id')->on('users')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Link to the payroll record this was deducted from (nullable for manual payments)
            $table->foreignId('payroll_record_id')
                  ->nullable()
                  ->constrained('payroll_records')
                  ->nullOnDelete();

            $table->decimal('amount', 10, 2);   // amount deducted this period
            $table->decimal('balance_after', 10, 2); // remaining balance after this payment

            $table->date('payment_date');
            $table->enum('payment_type', ['payroll_deduction', 'manual'])->default('payroll_deduction');

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
    }
};