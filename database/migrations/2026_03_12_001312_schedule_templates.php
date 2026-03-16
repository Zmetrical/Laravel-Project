<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. The Main Template
        Schema::create('schedule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Regular Office + Sat Half-Day"
            $table->unsignedInteger('grace_period_minutes')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. The Daily Breakdown
        Schema::create('schedule_template_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('schedule_templates')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->comment('0=Sun, 1=Mon, ..., 6=Sat');
            
            $table->boolean('is_working_day')->default(true);
            $table->time('shift_in')->nullable();  // Null if not a working day
            $table->time('shift_out')->nullable(); // Null if not a working day
            
            $table->timestamps();
            
            // Prevent duplicate days for the same template
            $table->unique(['template_id', 'day_of_week']); 
        });

        // 3. User Assignment (Remains the same)
        Schema::create('user_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);
            $table->foreignId('template_id')->constrained('schedule_templates')->onDelete('cascade');
            $table->date('effective_date');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_schedules');
        Schema::dropIfExists('schedule_template_days');
        Schema::dropIfExists('schedule_templates');
    }
};