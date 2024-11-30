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
        Schema::table('countries', function (Blueprint $table) {
            $table->longText('official_language')->nullable();
            $table->text('timezone')->nullable();
            $table->longText('flag_image')->nullable();
            $table->longText('flag_folder_path')->nullable();
            $table->text('capital')->nullable();
            $table->text('region')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            //
        });
    }
};
