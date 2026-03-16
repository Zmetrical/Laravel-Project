<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Core overtime request table.
         *
         * Flow:
         *  pending → approved → paid
         *  pending → rejected
         *
         * Key design decisions:
         *  - hours is pulled from attendance_records.overtime_hours at filing time
         *  - ot_type and rate_multiplier are snapshotted so history stays accurate
         *    even if overtime_rates config changes later
         *  - estimated_pay = hours × users.hourlyRate × rate_multiplier (snapshotted)
         *  - paid_at is stamped when payroll run picks up this request;
         *    this is sufficient for payroll linkage without a hard FK at this stage
         */
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);

            // ── OT Details ────────────────────────────────────────
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->string('ot_type', 100);           // snapshotted label e.g. "Regular Overtime + Night Shift"
            $table->decimal('rate_multiplier', 4, 2); // snapshotted from overtime_rates
            $table->decimal('estimated_pay', 12, 2);  // snapshotted at filing time
            $table->text('reason')->nullable();

            // ── Approval ──────────────────────────────────────────
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'paid',
            ])->default('pending');

            $table->string('reviewed_by', 10)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();

            // ── Payroll Linkage ───────────────────────────────────
            $table->timestamp('paid_at')->nullable(); // stamped when included in a payroll run

            // ── Constraints ───────────────────────────────────────
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['user_id', 'status']);
            $table->index('date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};