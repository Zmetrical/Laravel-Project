<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 10);
            $table->unsignedBigInteger('leave_type_id');
            $table->year('year');

            $table->decimal('entitled_days', 5, 2)->default(0);    
            $table->decimal('carried_over_days', 5, 2)->default(0); 
            $table->decimal('used_days', 5, 2)->default(0);         
            $table->decimal('pending_days', 5, 2)->default(0);      
            $table->decimal('balance', 5, 2)->default(0);          

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->unique(['user_id', 'leave_type_id', 'year'], 'unique_leave_balance');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};