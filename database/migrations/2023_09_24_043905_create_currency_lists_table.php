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
        Schema::create('currency_lists', function (Blueprint $table) {
            $table->id();

            $table->string('code');
            $table->string('name')->nullable();
            $table->string('symbol')->nullable();
            $table->longText('countries')->nullable()->default('[]');

            $table->longText('denomination')->nullable()->default('[]');

            $table->string('total_circulation')->nullable();

            $table->integer('ranking')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_lists');
    }
};
