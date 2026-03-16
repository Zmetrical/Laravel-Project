<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['1st-15th', '16th-end']);
            $table->tinyInteger('month')->unsigned(); // 1–12
            $table->smallInteger('year')->unsigned();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('pay_date');
            $table->enum('status', ['draft', 'processing', 'released', 'closed'])
                  ->default('draft');
            $table->string('processed_by', 10)->nullable();
            $table->string('released_by', 10)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate periods
            $table->unique(['period_type', 'month', 'year'], 'unique_payroll_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};