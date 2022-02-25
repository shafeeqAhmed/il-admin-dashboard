<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('app_review_uuid')->unique();
            $table->unsignedBigInteger('profile_id');
            $table->enum('type',['complain','suggestion','bug','feedback','query'])->default('feedback');
            $table->text('comments');
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
        Schema::dropIfExists('app_reviews');
    }
}
