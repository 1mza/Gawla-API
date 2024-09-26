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
        Schema::create('tourism_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('images'); // This column will store the image path
            $table->string('location');
            $table->text('description');
            $table->decimal('rate', 8, 2)->nullable(); // Rate can be decimal, representing the average rating
            $table->text('offers');
            $table->text('comments')->nullable();
            $table->Integer('phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourism_companies');
    }
};
