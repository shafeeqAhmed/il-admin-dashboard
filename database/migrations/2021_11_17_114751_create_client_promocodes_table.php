<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('client_promocode_uuid');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->on('clients')->references('id');
            $table->unsignedBigInteger('code_id');
            $table->foreign('code_id')->on('promo_codes')->references('id');
            $table->string('coupon_code');
            $table->boolean('is_archive')->default(false);
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
        Schema::dropIfExists('client_promocodes');
    }
}
