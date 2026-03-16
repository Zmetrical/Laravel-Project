<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // ── Identity ──────────────────────────────────────────
            $table->string('id', 10)->primary();
            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('fullName', 100);
            $table->enum('role', ['employee', 'hr', 'accounting', 'admin']);
            $table->enum('employmentStatus', ['probationary', 'regular', 'resigned', 'terminated'])->default('probationary');
            $table->boolean('isActive')->default(true);

            // ── Personal info ─────────────────────────────────────
            $table->string('firstName', 50)->nullable();
            $table->string('middleName', 50)->nullable();
            $table->string('lastName', 50)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dateOfBirth')->nullable();
            $table->string('civilStatus', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phoneNumber', 20)->nullable();

            // ── Address ───────────────────────────────────────────
            $table->string('addressStreet')->nullable();
            $table->string('addressBarangay', 100)->nullable();
            $table->string('addressCity', 100)->nullable();
            $table->string('addressProvince', 100)->nullable();
            $table->string('addressRegion', 100)->nullable();
            $table->string('addressZipCode', 10)->nullable();

            // ── Employment ────────────────────────────────────────
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('branch', 100)->default('Meycauayan Main');
            $table->date('hireDate')->nullable();

            // ── Compensation ──────────────────────────────────────
            $table->decimal('basicSalary', 10, 2)->default(0);
            $table->decimal('dailyRate', 10, 2)->default(0);
            $table->decimal('hourlyRate', 10, 2)->default(0);

            // ── Biometric ─────────────────────────────────────────
            $table->boolean('biometricEnrolled')->default(false);
            $table->string('enrolledFingerType', 50)->nullable();
            $table->timestamp('biometricEnrollmentDate')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id', 10)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};