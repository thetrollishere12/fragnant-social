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
        

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->bigInteger('plan_id')->nullable();
        });

        Schema::table('paypal_subscriptions', function (Blueprint $table) {
            $table->bigInteger('plan_id')->nullable();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });

        Schema::table('paypal_subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });


    }
};
