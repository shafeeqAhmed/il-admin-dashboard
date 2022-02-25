<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoatServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boat_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('qualification_uuid');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->string('title');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('boat_services');
    }
}
