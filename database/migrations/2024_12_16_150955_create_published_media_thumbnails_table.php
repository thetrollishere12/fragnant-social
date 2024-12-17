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
        Schema::create('published_media_thumbnails', function (Blueprint $table) {

            $table->id();



            $table->integer('published_media_id');

            $table->string('storage');

            $table->string('folder');

            $table->string('filename');
            


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_media_thumbnails');
    }
};
