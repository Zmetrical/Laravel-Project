<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')
                  ->constrained('payroll_periods')
                  ->cascadeOnDelete();
            $table->string('user_id', 10);
            $table->foreign('user_id')->references('id')->on('users');

            // ── Earnings ─────────────────────────────────────────────────
            $table->decimal('basic_pay', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('night_diff_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->decimal('rest_day_pay', 12, 2)->default(0);
            $table->decimal('leave_pay', 12, 2)->default(0);
            $table->decimal('additional_shift_pay', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);

            // ── Deductions ───────────────────────────────────────────────
            $table->decimal('sss', 10, 2)->default(0);
            $table->decimal('philhealth', 10, 2)->default(0);
            $table->decimal('pagibig', 10, 2)->default(0);
            $table->decimal('withholding_tax', 10, 2)->default(0);
            $table->decimal('late_deductions', 10, 2)->default(0);
            $table->decimal('undertime_deductions', 10, 2)->default(0);
            $table->decimal('absent_deductions', 10, 2)->default(0);
            $table->decimal('other_deductions', 10, 2)->default(0);

            // Carried over from previous period when net pay went negative
            // Only allowed on 1st-15th periods (never on 16th-end)
            $table->decimal('deferred_balance', 10, 2)->default(0);

            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);

            // ── Status ───────────────────────────────────────────────────
            $table->enum('status', ['draft', 'released'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            // One record per employee per period
            $table->unique(['payroll_period_id', 'user_id'], 'unique_employee_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};