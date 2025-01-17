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
        Schema::create('youtube_channels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('digital_asset_id')->constrained()->onDelete('cascade');
            
            $table->string('channel_id')->unique();
            $table->string('channel_name');
            $table->string('channel_url');
            $table->string('channel_image')->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_channels');
    }
};
