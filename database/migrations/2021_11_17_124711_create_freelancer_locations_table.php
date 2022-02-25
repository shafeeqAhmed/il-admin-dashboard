<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelancerLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freelancer_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('freelancer_location_uuid')->unique();
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('location_id');
            $table->foreign('location_id')->on('locations')->references('id');
            $table->enum('type',['primary', 'secondary']);
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('freelancer_locations');
    }
}
