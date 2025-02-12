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
        Schema::create('ebay_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('account_id'); // eBay user ID
            $table->integer('digital_asset_id'); // Foreign key to DigitalAsset
            $table->string('name')->nullable();
            $table->string('accountType')->nullable();
            $table->string('registrationMarketplaceId')->nullable();
            $table->string('url')->nullable(); // eBay profile URL
            $table->string('avatar_url')->nullable(); // eBay profile picture

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ebay_accounts');
    }
};
