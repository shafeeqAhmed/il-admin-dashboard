<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferredEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferred_earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('transferred_earning_uuid')->unique();
            $table->unsignedBigInteger('freelancer_withdrawal_id');
            $table->foreign('freelancer_withdrawal_id')->on('freelancer_withdrawal')->references('id');
            $table->unsignedBigInteger('freelancer_earning_id');
            $table->foreign('freelancer_earning_id')->on('freelancer_earnings')->references('id');
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
        Schema::dropIfExists('transferred_earnings');
    }
}
