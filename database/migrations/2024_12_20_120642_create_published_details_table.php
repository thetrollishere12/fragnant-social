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
        Schema::create('published_details', function (Blueprint $table) {
            $table->id();

            $table->integer('published_id');

            $table->integer('media_template_id')->nullable();

            $table->text('type')->nullable();

            $table->text('description')->nullable();

            $table->longText('attributes')->nullable()->default('[]');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_details');
    }
};
