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
        Schema::create('digital_assets', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id'); // Name of the profile or platform
            $table->string('name'); // Name of the profile or platform
            $table->string('image')->nullable(); // Path to the profile image (optional)
            $table->text('description')->nullable(); // Description of the profile (optional)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_assets');
    }
};
