<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('customer_uuid');
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('user_id')->on('users')->references('id');
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();
            $table->integer('onboard_count')->default(0);
            $table->enum('gender',['male','female'])->nullable();
            $table->enum('type',['regular','guest','admin','walkin_customer'])->default('regular');
            $table->string('address')->nullable();
            $table->string('address_comments')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
//categories
