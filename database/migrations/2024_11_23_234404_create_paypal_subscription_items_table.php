<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypal_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscription_id');
            $table->string('paypal_id');
            $table->string('paypal_product');
            $table->string('paypal_plan');
            $table->integer('quantity')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_subscription_items');
    }
};
