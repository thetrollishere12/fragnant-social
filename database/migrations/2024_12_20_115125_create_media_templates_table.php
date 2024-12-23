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
        Schema::create('media_templates', function (Blueprint $table) {

            $table->id();

            $table->integer('user_id');

            $table->text('title')->nullable();

            $table->text('platform')->nullable();

            $table->text('url')->nullable();
            

            $table->string('storage');

            $table->string('folder');

            $table->string('filename');

            $table->longText('tags')->nullable();

            $table->longText('attributes')->nullable()->default('[]');

            $table->string('type')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_templates');
    }
};
