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
        Schema::create('user_media', function (Blueprint $table) {
            $table->id();

            $table->string('code_id')->unique();

            $table->string('storage');

            $table->string('folder');

            $table->string('filename');

            $table->bigInteger('size')->nullable();

            $table->integer('user_id');
            
            $table->string('type');

            // $table->integer('project_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_media');
    }
};
