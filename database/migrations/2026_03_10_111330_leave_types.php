<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('leave_types', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->boolean('is_paid')->default(true);
    $table->unsignedSmallInteger('max_days_per_year')->default(15);
    $table->boolean('requires_approval')->default(true);
    $table->text('description')->nullable();

    // ── Carry-over ────────────────────────────────────────
    $table->boolean('is_carry_over_allowed')->default(false);
    $table->unsignedSmallInteger('max_carry_over_days')->default(0);

    // ── Gender restriction (for ML/PL) ────────────────────
    $table->enum('applicable_to', ['all', 'male', 'female'])->default('all');

    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};