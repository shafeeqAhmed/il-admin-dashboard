<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportedPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     *
     */
    public function up()
    {
        Schema::create('reported_posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('reported_post_uuid');
            $table->unsignedBigInteger('post_id');
            $table->foreign('post_id')->on('posts')->references('id');
            $table->unsignedBigInteger('reporter_id');
            $table->enum('reported_type',['freelancer', 'customer', 'admin'])->default('customer');
            $table->text('comments');
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
        Schema::dropIfExists('reported_posts');
    }
}
