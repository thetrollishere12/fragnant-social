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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('subscription_product_id');

            $table->boolean('status')->default(0);

            $table->string('name')->nullable();

            $table->string('icon_image')->nullable();

            $table->longText('images')->default('[]')->nullable();

            $table->longText('description')->nullable();
            
            $table->integer('recurring_count')->nullable()->default(1);
            $table->string('recurring_type')->nullable();
            $table->string('payment_type')->nullable();
            $table->decimal('sale_price',65,2)->nullable();
            $table->decimal('price',65,2)->nullable();
            $table->string('currency')->nullable();
            $table->string('stripe_plan_id')->nullable();
            $table->string('paypal_plan_id')->nullable();
            
            $table->longText('benefits')->nullable()->default('[]');
            
            $table->integer('time_limit_amount')->nullable();

            $table->integer('trial_period_days')->nullable();
            $table->longText('exclusive_to_user_id')->nullable()->default('[]');
                
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
        Schema::dropIfExists('subscription_plans');
    }
};
