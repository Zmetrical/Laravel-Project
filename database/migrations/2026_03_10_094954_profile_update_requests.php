<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_update_requests', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('employeeId', 10);
            $table->string('employeeName');
            $table->string('field', 100);
            $table->text('oldValue')->nullable();
            $table->text('newValue');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->datetime('submittedDate');
            $table->string('reviewedBy')->nullable();
            $table->datetime('reviewDate')->nullable();
            $table->timestamps();

            $table->foreign('employeeId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_update_requests');
    }
};