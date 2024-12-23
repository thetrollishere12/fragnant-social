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
        Schema::create('published_asset_maps', function (Blueprint $table) {
            $table->id();

            $table->integer('published_id');

            $table->integer('user_media_id');

            $table->longText('attributes')->nullable()->default('[]');

            $table->integer('weight')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_asset_maps');
    }
};
