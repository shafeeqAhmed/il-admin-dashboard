<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelancerEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freelancer_earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->double('earned_amount')->nullable();
            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')->on('purchases')->references('id');
            $table->date('amount_due_on')->nullable();
            $table->char('currency',10)->nullable();
            $table->unsignedBigInteger('freelancer_withdrawal_id');
            $table->foreign('freelancer_withdrawal_id')->on('freelancer_withdrawal')->references('id');
            $table->boolean('is_blocked')->default(false);
            $table->enum('status',['transferred', 'pending', 'on_hold']);
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
        Schema::dropIfExists('freelancer_earnings');
    }
}
