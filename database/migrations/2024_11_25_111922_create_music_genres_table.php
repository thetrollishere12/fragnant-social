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
        Schema::create('music_genres', function (Blueprint $table) {
            $table->id(); // BigInt primary key, auto-increment
            $table->string('name'); // Name of the genre
            $table->string('slug')->unique(); // URL-friendly identifier
            $table->text('description')->nullable(); // Optional description
            $table->boolean('is_active')->default(true); // Active status
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('music_genres');
    }
};
