<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Single-row settings table for OT limits and enforcement behavior.
         * Seeded with DOLE-compliant defaults on first run.
         *
         * enforce_limit controls system behavior when a limit is exceeded:
         *  1 → block   — submission is rejected outright
         *  0 → warn    — warning is shown but employee can still file;
         *                HR sees the flag and decides on approval
         *
         * DOLE standard defaults:
         *  daily_max_hours   → 8 hrs
         *  weekly_max_hours  → 48 hrs
         *  monthly_max_hours → 192 hrs
         */
        Schema::create('overtime_configurations', function (Blueprint $table) {
            $table->id();
            $table->decimal('daily_max_hours', 4, 2)->default(8.00);
            $table->decimal('weekly_max_hours', 5, 2)->default(48.00);
            $table->decimal('monthly_max_hours', 6, 2)->default(192.00);

            // ── Enforcement ───────────────────────────────────────
            $table->boolean('enforce_limit')->default(true)
                  ->comment('true = block submission, false = warn only');

            // ── Who last updated ──────────────────────────────────
            $table->string('updated_by', 10)->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_configurations');
    }
};