<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('media_uuid')->unique();
            $table->unsignedBigInteger('post_id');
            $table->foreign('post_id')->on('posts')->references('id');
            $table->string('media_src');
            $table->string('video_thumbnail')->nullable();
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('media_type',['image', 'video'])->nullable();
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
        Schema::dropIfExists('post_media');
    }
}
