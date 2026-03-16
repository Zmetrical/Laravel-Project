<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Every change to a leave balance writes a row here.
         * This is the source of truth for the audit trail.
         *
         * Types:
         *  accrual       — yearly/monthly credit (e.g. 15 days on Jan 1)
         *  usage         — approved leave request consumed days
         *  adjustment    — manual correction by HR/admin
         *  carry_over    — balance moved from previous year
         *  carry_expire  — unused balance that was NOT carried over (expired)
         *  reversal      — undo a previous usage (e.g. rejected after payroll)
         */
        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);
            $table->unsignedBigInteger('leave_type_id');
            $table->year('year');

            $table->enum('transaction_type', [
                'accrual',
                'usage',
                'adjustment',
                'carry_over',
                'carry_expire',
                'reversal',
            ]);

            $table->decimal('days', 5, 2);              // positive = credit, negative = deduction
            $table->decimal('balance_after', 5, 2);     // snapshot of balance after this transaction

            // ── Reference ─────────────────────────────────────────
            $table->nullableMorphs('reference');        // polymorphic: leave_requests, payroll_records, etc.
            $table->text('remarks')->nullable();

            // ── Who did it ────────────────────────────────────────
            $table->string('created_by', 100)->nullable(); // user who triggered (system = null or 'system')

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');

            $table->index(['user_id', 'leave_type_id', 'year']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_transactions');
    }
};