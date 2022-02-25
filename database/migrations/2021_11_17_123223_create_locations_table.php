<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('location_uuid')->unique();
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('story_id')->nullable();
            $table->foreign('story_id')->on('stories')->references('id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->foreign('post_id')->on('posts')->references('id');
            $table->unsignedBigInteger('place_id')->nullable();
            $table->string('address');
            $table->string('route')->nullable();
            $table->string('street_number')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('country_code')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('location_id')->nullable();
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
        Schema::dropIfExists('locations');
    }
}
