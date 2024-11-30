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
        Schema::create('webpages', function (Blueprint $table) {
            $table->id();
            $table->longText('uri')->nullable();
            $table->longText('name')->nullable();
            $table->longText('title')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('indexable')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webpages');
    }
};
