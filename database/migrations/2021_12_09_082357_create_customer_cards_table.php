<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('customer_card_uuid')->unique();
            $table->string('card_id')->comment('scr_id we receive from checkout for card detail');
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->on('customers')->references('id');
            $table->string('bin')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_name')->nullable();
            $table->string('last_digits')->nullable();
            $table->string('expiry')->nullable();
            $table->string('token')->nullable();
            $table->string('customer_checkout_id')->nullable();
            $table->boolean('use_again')->default(false);
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
        Schema::dropIfExists('customer_cards');
    }
}
