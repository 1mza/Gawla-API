<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('images')->nullable(); // Store multiple image URLs as JSON
            $table->string('location');
            $table->text('description');
            $table->decimal('rate', 8, 2)->nullable();
            $table->boolean('wifi')->default(false);
            $table->boolean('pool')->default(false);
            $table->boolean('car_parking')->default(false);
            $table->tinyInteger('sustainable_travel_level')->nullable();
            $table->enum('disability_accommodation', ['none', 'hearing', 'physical'])->default('none');
            $table->integer('price');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};


