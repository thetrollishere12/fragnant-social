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
        Schema::create('paypal_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('paypal_id');
            $table->string('paypal_plan');
            $table->string('paypal_status');
            $table->integer('quantity')->nullable()->default(null);
            $table->timestamp('trial_ends_at')->nullable()->default(null);
            $table->timestamp('ends_at')->nullable()->default(null);
            $table->timestamps();
            $table->string('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paypal_subscriptions');
    }
};
