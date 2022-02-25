<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_uuid');
            $table->enum('profile_type',['customer','freelancer','walkin_customer']);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->enum('gender',['male','female']);
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('address_comments')->nullable();
            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('country_code')->nullable();
            $table->string('country_name')->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->string('profile_card_image')->nullable();
            $table->string('cover_video')->nullable();
            $table->string('cover_video_thumb')->nullable();
            $table->boolean('has_bank_detail')->default(false);
            $table->string('cover_image')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->integer('onboard_count')->default(false);
            $table->string('default_currency')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_login')->default(false);
            $table->boolean('public_chat')->default(false);
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
        Schema::dropIfExists('users');
    }
}
