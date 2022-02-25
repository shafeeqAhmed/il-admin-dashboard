<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('post_uuid');
            $table->unsignedBigInteger('freelancer_id');
            $table->foreign('freelancer_id')->on('freelancers')->references('id');
            $table->text('caption')->nullable();
            $table->text('text')->nullable();
            $table->enum('post_type',['paid','unpaid']);
            $table->enum('media_type',['image', 'video']);
            $table->enum('status',['pending','approved','cancelled','rejected'])->default('approved');
            $table->string('url')->nullable();
            $table->string('local_path')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->integer('part_no')->nullable();
            $table->boolean('is_intro')->default(false);
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
        Schema::dropIfExists('posts');
    }
}
