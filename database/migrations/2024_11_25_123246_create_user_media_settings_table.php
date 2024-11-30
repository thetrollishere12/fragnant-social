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
        Schema::create('user_media_settings', function (Blueprint $table) {
            
            $table->id();
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->longText('video_type_id')->nullable(); // Type of video
            $table->longText('music_genre_id')->nullable(); // Foreign key to music_genres
            $table->integer('frequency')->nullable(); // How often (1,2,3)
            $table->string('frequency_type')->nullable(); // How often (e.g., daily, weekly)
            $table->integer('quantity')->default(1); // How many

            $table->boolean('user_audio')->nullable(); // How many

            $table->timestamps();

      

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_media_settings');
    }
};
