<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * OT rate multiplier config — seedable, not user-generated.
         * Stored separately so rates can be updated without touching request history.
         *
         * The rate_multiplier is snapshotted into overtime_requests at filing time
         * so historical records stay accurate even if these rates change later.
         *
         * Examples:
         *  Regular Overtime                      → 1.25
         *  Regular Overtime + Night Shift        → 1.375
         *  Rest Day Overtime                     → 1.69
         *  Rest Day Overtime + Night Shift       → 1.859
         *  Special Holiday Overtime              → 1.69
         *  Special Holiday Overtime on Rest Day  → 1.95
         *  Regular Holiday Overtime              → 2.60
         *  Regular Holiday Overtime on Rest Day  → 3.38
         */
        Schema::create('overtime_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('multiplier', 4, 2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_rates');
    }
};