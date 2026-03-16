<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
        $table->id();
        $table->string('user_id', 10);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0.00);
            $table->decimal('late_minutes', 5, 2)->default(0.00);
            $table->decimal('undertime_minutes', 5, 2)->default(0.00);
            $table->decimal('overtime_hours', 5, 2)->default(0.00);

            $table->enum('status', [
                'present',
                'absent',
                'late',
                'half_day',
                'leave',
                'holiday',
                'rest_day',
                'incomplete',
            ])->default('incomplete');

            $table->text('notes')->nullable();
            $table->boolean('is_biometric')->default(false)
                  ->comment('True if synced from biometric device');

            $table->timestamps();

            $table->unique(['user_id', 'date'], 'uq_user_date');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};