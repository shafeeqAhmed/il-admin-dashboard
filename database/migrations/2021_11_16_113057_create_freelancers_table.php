<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelancersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freelancers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->unique();
            $table->uuid('freelancer_uuid');
            $table->text('bio')->nullable();
            $table->float('price')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('onboard_count')->default(0);
            $table->string('cover_video_thumb')->nullable();
            $table->string('profile_card_image')->nullable();
            $table->string('default_currency')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('cover_video')->nullable();
            $table->enum('booking_preferences',['male', 'female', 'both'])->default('both');
            $table->integer('profile_type')->default(0)->comment('0 for not completed,1 for booking,2 for subscription,3 for both');
            $table->boolean('public_chat')->default(0);
            $table->boolean('is_archive')->default(0);
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
        Schema::dropIfExists('freelancers');
    }
}
