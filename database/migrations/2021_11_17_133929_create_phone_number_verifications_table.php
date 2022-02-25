<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneNumberVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_number_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('code_uuid');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->on('users')->references('id');
            $table->string('phone_number')->nullable();
            $table->string('country_code');
            $table->string('country_name');
            $table->string('email')->nullable();
            $table->string('verification_code')->nullable();
            $table->dateTime('code_expires_at')->nullable();
            $table->enum('status',['verified', 'not_verified'])->default('not_verified');
            $table->enum('type',['signup', 'forget_password'])->default('signup');
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
        Schema::dropIfExists('phone_number_verifications');
    }
}
