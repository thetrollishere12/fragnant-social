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
        Schema::create('tiktok_tokens', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('digital_asset_id');
            $table->text('platform_id');
            $table->string('platform')->default('tiktok');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->text('scopes'); // Add this line
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_tokens');
    }
};
