<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('review_uuid')->unique();
            $table->unsignedBigInteger('reviewer_id');
            $table->foreign('reviewer_id')->on('freelancers')->references('id');
            $table->unsignedBigInteger('reviewed_id');
            $table->foreign('reviewed_id')->on('customers')->references('id');
            $table->unsignedBigInteger('content_id');
            $table->enum('type',['subscription', 'appointment', 'class'])->default('subscription');
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            $table->boolean('is_review')->default(true);
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
        Schema::dropIfExists('reviews');
    }
}
