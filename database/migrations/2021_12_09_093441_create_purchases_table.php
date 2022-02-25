<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('purchases_uuid')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->on('customers')->references('id');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->dateTime('purchase_datetime')->nullable();
            $table->enum('type',['appointment'])->nullable();
            $table->enum('purchased_by',['card'])->nullable();
            $table->string('purchased_in_currency')->nullable();
            $table->string('service_provider_currency')->nullable();
            $table->float('conversion_rate')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->foreign('appointment_id')->on('appointments')->references('id');
            $table->unsignedBigInteger('customer_card_id')->nullable();
            $table->foreign('customer_card_id')->on('customer_cards')->references('id');
            $table->double('boatek_fee')->nullable();
            $table->double('transaction_charges')->nullable();
            $table->double('service_amount')->nullable();
            $table->double('total_amount')->nullable();
            $table->double('discount')->nullable();
            $table->enum('discount_type',['percentage', 'fixed'])->nullable();
            $table->double('total_amount_percentage')->nullable();
            $table->double('tax')->nullable();
            $table->unsignedBigInteger('promo_code_id');
            $table->foreign('promo_code_id')->on('promo_codes')->references('id');
            $table->double('boatek_fee_percenatge')->nullable();
            $table->boolean('is_refund')->default(false);
            $table->enum('status',['canceled','pending','failed','rejected','succeeded','refunded'])->nullable();
            $table->boolean('is_archived')->default(false);
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
        Schema::dropIfExists('purchases');
    }
}
