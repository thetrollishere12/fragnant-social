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
        Schema::create('product_import_feeds', function (Blueprint $table) {
            $table->id();

            $table->integer('digital_asset_id');
            $table->string('name')->nullable(); // Name of the feed
            $table->string('file_type'); // CSV, XLSX, API, Manual
            $table->string('url'); // URL of the file

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_import_feeds');
    }
};
