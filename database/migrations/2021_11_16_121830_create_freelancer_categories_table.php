<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreelancerCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('freelancer_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('freelancer_category_uuid');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->on('categories')->references('id');
            $table->unsignedBigInteger('sub_category_id');
            $table->foreign('sub_category_id')->on('sub_categories')->references('id');
            $table->string('name')->nullable();
            $table->string('currency')->nullable();
            $table->string('price')->nullable();
            $table->boolean('is_online')->default(false);
            $table->integer('duration')->nullable();
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
        Schema::dropIfExists('freelancer_categories');
    }
}
