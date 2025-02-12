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
        Schema::create('platform_tokens', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id');

            $table->unsignedBigInteger('digital_asset_id');

            $table->string('platform');
            $table->text('platform_id')->nullable();
            $table->longText('access_token');
            $table->longText('refresh_token')->nullable();
            $table->longText('scopes'); // Add this line
            $table->dateTime('expires_at')->nullable();
            $table->longText('attributes')->nullable(); // Add this line

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_tokens');
    }
};
