<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Philippine public holidays per year.
         * Seeded annually (or via admin panel).
         *
         * Types:
         *  regular — double pay, counted as holiday pay
         *  special — 30% premium, no work = no pay
         */
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->date('date');
            $table->enum('type', ['regular', 'special']);
            $table->smallInteger('year')->unsigned();

            $table->index('date');
            $table->index('year');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};